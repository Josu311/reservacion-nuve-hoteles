<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\HotelConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    private function resolveDashboardHotelScope(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user && $user->isDashboardAdmin(), 403, 'No tienes permiso para acceder a este recurso');

        $requestedHotelCode = $request->input('hotel_code');
        if ($requestedHotelCode !== null) {
            $request->validate([
                'hotel_code' => ['string', 'in:' . implode(',', HotelConfig::codes())],
            ]);
            $requestedHotelCode = HotelConfig::normalize($requestedHotelCode);
        }

        if ($user->isSuperAdmin()) {
            return [
                'selected_hotel_code' => $requestedHotelCode,
                'allowed_hotel_codes' => HotelConfig::codes(),
            ];
        }

        $assignedHotelCode = $user->assignedHotelCode();
        abort_unless($assignedHotelCode, 403, 'Tu cuenta no tiene un hotel asignado, contacta al administrador');

        if ($requestedHotelCode !== null && !$user->canAccessHotel($requestedHotelCode)) {
            abort(403, 'No tienes permiso para consultar este hotel');
        }

        return [
            'selected_hotel_code' => $assignedHotelCode,
            'allowed_hotel_codes' => [$assignedHotelCode],
        ];
    }

    private function resolveDashboardDateFilter(Request $request): array
    {
        $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return [
            'has_date_filter' => $startDate !== null || $endDate !== null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_at' => $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : null,
            'end_at' => $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : null,
        ];
    }

    private function applyDashboardDateFilter($query, array $dateFilter, string $column = 'created_at')
    {
        return $query
            ->when($dateFilter['start_at'], fn ($query, $startAt) => $query->where($column, '>=', $startAt))
            ->when($dateFilter['end_at'], fn ($query, $endAt) => $query->where($column, '<=', $endAt));
    }

    private function dashboardFiltersPayload(?string $hotelCode, array $dateFilter): array
    {
        return [
            'hotel_code' => $hotelCode,
            'has_date_filter' => $dateFilter['has_date_filter'],
            'start_date' => $dateFilter['start_date'],
            'end_date' => $dateFilter['end_date'],
        ];
    }

    private function whereDashboardSale($query)
    {
        return $query
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->where(function ($query) {
                $query
                    ->whereNull('is_confirmed')
                    ->orWhere('is_confirmed', '!=', Reservation::CONFIRMATION_CANCELLED);
            });
    }

    private function whereDashboardTotal($query)
    {
        return $query->where(function ($query) {
            $query
                ->whereIn('status', ['paid', 'booking_in_reception'])
                ->orWhere('is_confirmed', Reservation::CONFIRMATION_CANCELLED);
        });
    }

    public function dashboardData(Request $request) {
        $scope = $this->resolveDashboardHotelScope($request);
        $hotelCode = $scope['selected_hotel_code'];
        $dateFilter = $this->resolveDashboardDateFilter($request);
        $generalQuery = Reservation::query()
            ->when($hotelCode, fn ($query) => $query->where('hotel_code', $hotelCode));
        $this->applyDashboardDateFilter($generalQuery, $dateFilter);

        $year = now()->year;

        $rows = (clone $generalQuery)
            ->tap(fn ($query) => $this->whereDashboardSale($query))
            ->when(!$dateFilter['has_date_filter'], fn ($query) => $query->whereYear('created_at', $year))
            ->selectRaw('MONTH(created_at) as month, SUM(amount_cents) as total_cents')
            ->groupBy('month')
            ->pluck('total_cents', 'month'); // [1 => 120000, 3 => 45000, ...]

        $rowCollect = collect(range(1, 12))
            ->map(fn ($m) => (int) ($rows[$m] ?? 0))
            ->all();

        $totalCentsPerMonth = array_map(function($cents) {
            if($cents != 0) {
                return $cents / 100;
            }

            return 0;
        }, $rowCollect);

        $totalCents = (clone $generalQuery)
            ->tap(fn ($query) => $this->whereDashboardTotal($query))
            ->sum('amount_cents');

        $totalCentsWeekly = (clone $generalQuery)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->tap(fn ($query) => $this->whereDashboardSale($query))
            ->sum('amount_cents');

        $totalCentsMonthly = (clone $generalQuery)
            ->tap(fn ($query) => $this->whereDashboardSale($query))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_cents');

        $totalCentsDaily = (clone $generalQuery)
            ->tap(fn ($query) => $this->whereDashboardSale($query))
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount_cents');

        $totalReservations = (clone $generalQuery)
            ->tap(fn ($query) => $this->whereDashboardTotal($query))
            ->count();

        $totalReservationsWeekly = (clone $generalQuery)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->tap(fn ($query) => $this->whereDashboardSale($query))
            ->count();

        $rows = (clone $generalQuery)
        ->tap(fn ($query) => $this->whereDashboardSale($query))
        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->selectRaw('WEEKDAY(created_at) as weekday, COUNT(*) as total')
        ->groupBy('weekday')
        ->pluck('total', 'weekday'); // [0=>3, 2=>5...]

        $reservations_weekly = collect(range(0, 6))
        ->map(fn ($d) => (int) ($rows[$d] ?? 0))
        ->all();

        $usersReservationsQuery = Reservation::query()
            ->from('reservations as r')
            ->when($hotelCode, fn ($query) => $query->where('r.hotel_code', $hotelCode))
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->whereIn('r.status', ['paid', 'booking_in_reception']);
        $this->applyDashboardDateFilter($usersReservationsQuery, $dateFilter, 'r.created_at');

        $usersReservations = $usersReservationsQuery
            ->select([
                'r.id',
                'r.status',
                'r.hotel_code',
                'r.description',
                'r.is_confirmed',
                'r.amount_cents',
                'r.provider_folio',
                'r.origin_page',
                'r.room_type_code',
                'r.checkin',
                'r.checkout',
                'r.created_at',
                DB::raw('COALESCE(u.name, r.guest_name) as customer_name'),
                DB::raw('COALESCE(u.email, r.guest_email) as customer_email'),
                DB::raw('COALESCE(u.phone, r.guest_phone) as customer_phone'),
            ])
            ->orderByDesc('r.created_at')
            ->get();

        return response()->json([
            'allowed_hotel_codes' => $scope['allowed_hotel_codes'],
            'selected_hotel_code' => $hotelCode,
            'total_cents_per_month' => $totalCentsPerMonth,
            'total_amount_cents' => (int) $totalCents,
            'total_amount_cents_daily' => (int) $totalCentsDaily,
            'total_amount_cents_weekly' => (int) $totalCentsWeekly,
            'total_amount_cents_monthly' => (int) $totalCentsMonthly,
            'total_reservations' => (int) $totalReservations,
            'total_reservations_weekly' => (int) $totalReservationsWeekly,
            'reservations_weekly' => $reservations_weekly,
            'user_reservations' => $usersReservations,
            'filters' => $this->dashboardFiltersPayload($hotelCode, $dateFilter),
        ], 200);
    }

    public function paginateReservations(Request $request)
    {
        $scope = $this->resolveDashboardHotelScope($request);
        $generalQuery = Reservation::query();
        $hotelCode = $scope['selected_hotel_code'];
        $dateFilter = $this->resolveDashboardDateFilter($request);
        
        // Params desde el front (con defaults)
        $page    = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        // límites razonables (evitar que pidan 999999)
        $perPage = max(1, min($perPage, 100));

        $query = (clone $generalQuery)
            ->from('reservations as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->when($hotelCode, fn ($query) => $query->where('r.hotel_code', $hotelCode))
            ->whereIn('r.status', ['paid', 'booking_in_reception']);
        $this->applyDashboardDateFilter($query, $dateFilter, 'r.created_at');

        $query = $query
            ->select([
                'r.id',
                'r.status',
                'r.hotel_code',
                'r.description',
                'r.is_confirmed',
                'r.amount_cents',
                'r.provider_folio',
                'r.origin_page',
                'r.room_type_code',
                'r.checkin',
                'r.checkout',
                'r.created_at',
                DB::raw('COALESCE(u.name, r.guest_name) as customer_name'),
                DB::raw('COALESCE(u.email, r.guest_email) as customer_email'),
                DB::raw('COALESCE(u.phone, r.guest_phone) as customer_phone'),
            ])
            ->orderByDesc('r.created_at');

        $paginator = $query->paginate(
            perPage: $perPage,
            columns: ['*'],
            pageName: 'page',
            page: $page
        );

        // Respuesta “amigable” para Vue/Element Plus
        return response()->json([
            'data' => $paginator->items(),
            'allowed_hotel_codes' => $scope['allowed_hotel_codes'],
            'selected_hotel_code' => $hotelCode,
            'filters' => $this->dashboardFiltersPayload($hotelCode, $dateFilter),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], 200);
    }


    public function addDescription(Request $request) {
        $request->validate([
            'id' => 'required|int',
            'description' => 'required|string'
        ]);

        $reservation = Reservation::find($request->input('id'));
        $reservation->update(['description' => $request->input('description')]);

        return response()->json(['message' => 'Descripción actualizada correctamente', 'reservation' => $reservation], 200);
    }

    public function changeIsConfirmed(Request $request) {
        $request->validate([
            'id' => 'required|int',
            'is_confirmed' => 'required|boolean'
        ]);

        $reservation = Reservation::find($request->input('id'));
        $reservation->update(['is_confirmed' => $request->input('is_confirmed')]);

        if($request->input('is_confirmed'))
            return response()->json(['message' => 'Se confirmó la reservación'], 200);
        else
            return response()->json(['message' => 'Se canceló la reservación'], 200);
    }

    public function loginDashboard(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if(!$user) return response()->json([
            'message' => 'Credenciales inválidas'
        ], 401);

        if(!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if(!$user->rol_id) return response()->json([
            'message' => 'Tu cuenta no tiene un rol asignado, contacta al administrador'
        ], 403);

        if($user->rol_id == 1) return response()->json([
            'message' => 'No tienes permiso para acceder a este recurso'
        ], 403);

        if((int) $user->rol_id === 2 && !$user->assignedHotelCode()) {
            return response()->json([
                'message' => 'Tu cuenta no tiene un hotel asignado, contacta al administrador'
            ], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Ok',
        ], 200);
    }

    public function logoutDashboard(Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

        return response()->json(['message' => 'Cierre de sesión exitoso'], 200);
    }

    public function markReservationAsCancelled(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
        ]);

        $scope = $this->resolveDashboardHotelScope($request);
        $hotelCode = $scope['selected_hotel_code'];

        $reservation = Reservation::query()
            ->when($hotelCode, fn ($query) => $query->where('hotel_code', $hotelCode))
            ->findOrFail($request->input('id'));

        $reservation->update([
            'is_confirmed' => Reservation::CONFIRMATION_CANCELLED,
        ]);

        return response()->json([
            'message' => 'Se marcó la reservación como cancelada',
            'reservation' => $reservation->fresh(),
        ], 200);
    }
}
