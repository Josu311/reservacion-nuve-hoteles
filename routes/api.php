<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DashboardController;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/get-states', [ApiController::class, 'getStates']);
Route::post('/get-cities', [ApiController::class, 'getCities']);
Route::post('/get-address', [ApiController::class, 'getAddress']);

Route::get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
    ]);
})->middleware('auth:sanctum');


Route::get('/dashboard-data', [DashboardController::class, 'dashboardData'])->middleware('auth:sanctum');
Route::get('/paginate-reservations', [DashboardController::class, 'paginateReservations'])->middleware('auth:sanctum');
Route::prefix('/reservation')->group(function() {
    Route::post('/add-description', [DashboardController::class, 'addDescription'])->name('add.description');
    Route::patch('/change-is-confirmed', [DashboardController::class, 'changeIsConfirmed']);
    Route::patch('/mark-as-cancelled', [DashboardController::class, 'markReservationAsCancelled'])->name('reservation.mark-as-cancelled');
})->middleware('auth:sanctum');

Route::get('datos-enzo', function() {
        $currentMonthlyReservation = Reservation::query()
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();

        $currentMonthlyReservationMount = Reservation::query()
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->selectRaw('SUM(amount_cents) as total_cents')
                                            ->groupBy('created_at')
                                            ->first();

        return response()->json([
            'reservaciones en el mes' => $currentMonthlyReservation,
            'reservaciones costo' => $currentMonthlyReservationMount,
        ]);
});