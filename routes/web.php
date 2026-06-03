<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('index');

Route::get('/quienes-somos', function () {
    return Inertia::render('AboutUs');
})->name('about-us');

Route::get('/nuestros-hoteles', function () {
    return Inertia::render('Hotels');
})->name('hotels');

Route::get('/experiencias', function () {
    return Inertia::render('Experiences');
})->name('experiences');

Route::prefix('disponibilidad')->group(function () {
    Route::get('/', [ReservaController::class, 'index'])->name('disponibilidad.index');
    Route::post('/', [ReservaController::class, 'validateDataUserHabs'])->name('disponibilidad.consultar');
    Route::post('/cupon/validate', [ReservaController::class, 'validateCoupon'])->name('disponibilidad.coupon.validate');
});

Route::get('/parras/reservar', function () {
    return Inertia::render('ParrasBooking');
})->name('parras.booking');

Route::prefix('parras/disponibilidad')->group(function () {
    Route::get('/', [ReservaController::class, 'hotelIndex'])
        ->defaults('hotel', 'parras')
        ->name('parras.disponibilidad.index');
    Route::post('/', [ReservaController::class, 'validateDataUserHabsForHotel'])
        ->defaults('hotel', 'parras')
        ->name('parras.disponibilidad.consultar');
});

Route::post('/create-booking-reception', [CheckoutController::class, 'bookingInReception']);

Route::prefix('checkout')->group(function () {
    Route::post('/', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/stripe/webhook/{hotel?}', [StripeWebhookController::class, 'handle'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('stripe.webhook');
    Route::get('/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/success/reception', [CheckoutController::class, 'pagoEnRecepcion']);

    Route::get('/status', [CheckoutController::class, 'status'])
        ->name('checkout.status')
        ->middleware('throttle:60,1');
});

Route::get('/thanks', function () {
    return Inertia::render('Checkout/Thanks');
})->name('checkout.thanks');

Route::middleware('auth')->group(function () {
    Route::get('/mis-compras', [ProfileController::class, 'purchases'])->name('profile.purchases');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/logout-dashboard', [DashboardController::class, 'logoutDashboard'])->middleware('auth:sanctum')->name('logout.dashboard');
Route::middleware('web')->post('/login-dashboard', [DashboardController::class, 'loginDashboard'])->name('login.dashboard');

require __DIR__ . '/auth.php';
