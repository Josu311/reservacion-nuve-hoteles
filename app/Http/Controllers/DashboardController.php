<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\HotelConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function PHPSTORM_META\map;

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

    public function dashboardData(Request $request) {
        $scope = $this->resolveDashboardHotelScope($request);
        $hotelCode = $scope['selected_hotel_code'];
        $generalQuery = Reservation::query()
            ->when($hotelCode, fn ($query) => $query->where('hotel_code', $hotelCode));

        $year = now()->year;

        $rows = (clone $generalQuery)
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->whereYear('created_at', $year)
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

        $totalCents = (clone $generalQuery)->whereIn('status', ['paid', 'booking_in_reception'])->sum('amount_cents');

        $totalCentsWeekly = (clone $generalQuery)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->sum('amount_cents');

        $totalCentsMonthly = (clone $generalQuery)
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_cents');

        $totalCentsDaily = (clone $generalQuery)
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount_cents');

        $totalReservations = (clone $generalQuery)->whereIn('status', ['paid', 'booking_in_reception'])->count();

        $totalReservationsWeekly = (clone $generalQuery)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereIn('status', ['paid', 'booking_in_reception'])
            ->count();

        $rows = (clone $generalQuery)
        ->whereIn('status', ['paid', 'booking_in_reception'])
        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->selectRaw('WEEKDAY(created_at) as weekday, COUNT(*) as total')
        ->groupBy('weekday')
        ->pluck('total', 'weekday'); // [0=>3, 2=>5...]

        $reservations_weekly = collect(range(0, 6))
        ->map(fn ($d) => (int) ($rows[$d] ?? 0))
        ->all();

        $usersReservations = Reservation::query()
            ->from('reservations as r')
            ->when($hotelCode, fn ($query) => $query->where('r.hotel_code', $hotelCode))
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->whereIn('r.status', ['paid', 'booking_in_reception'])
            ->select([
                'r.id',
                'r.status',
                'r.hotel_code',
                'r.description',
                'r.is_confirmed',
                'r.amount_cents',
                'r.provider_folio',
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
        ], 200);
    }

    public function paginateReservations(Request $request)
    {
        $scope = $this->resolveDashboardHotelScope($request);
        $generalQuery = Reservation::query();
        $hotelCode = $scope['selected_hotel_code'];
        
        // Params desde el front (con defaults)
        $page    = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        // límites razonables (evitar que pidan 999999)
        $perPage = max(1, min($perPage, 100));

        $query = (clone $generalQuery)
            ->from('reservations as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->when($hotelCode, fn ($query) => $query->where('r.hotel_code', $hotelCode))
            ->whereIn('r.status', ['paid', 'booking_in_reception'])
            ->select([
                'r.id',
                'r.status',
                'r.hotel_code',
                'r.description',
                'r.is_confirmed',
                'r.amount_cents',
                'r.provider_folio',
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
}
