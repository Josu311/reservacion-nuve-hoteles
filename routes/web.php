<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\StripeWebhookController;
use App\Mail\BookingInReceptionAdminMail;
use App\Mail\BookingInReceptionCustomerMail;
use App\Mail\ReservationConfirmedMail;
use App\Models\Reservation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Resend\Laravel\Facades\Resend;

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
});

Route::post('/create-booking-reception', function(Request $request) {
    $request->validate([
        'room_code' => 'required|string',
        'room_name' => 'required|string',
        'plan' => 'required|string',
        'check_in' => 'required|date',
        'check_out' => 'required|date',
        'adults' => 'required|integer|min:1',
        'num_habs' => 'required|integer|min:1',
        'user_info' => 'required|array',
        'user_info.name' => 'required|string',
        'user_info.lastname' => 'required|string',
        'user_info.email' => 'required|email',
        'user_info.phone' => 'required|string',
    ]);

    Reservation::create([
        'room_type_code' => $request->input('room_code'),
        'checkin' => $request->input('check_in'),
        'checkout' => $request->input('check_out'),
        'nights' => (new DateTime($request->input('check_out')))->diff(new DateTime($request->input('check_in')))->days,
        'rooms' => $request->input('num_habs'),
        'adults' => $request->input('adults'),
        'guest_name' => $request->input('user_info.name') . ' ' . $request->input('user_info.lastname'),
        'guest_email' => $request->input('user_info.email'),
        'guest_phone' => $request->input('user_info.phone'),
        'amount_cents' => $request->input('amount_cents'),
        'provider_folio' => 'RECEPCION-' . strtoupper(uniqid()),
        'provider_hold_expires_at' => now()->addHours(2),
        'status' => 'booking_in_reception',
        'client_order_key' => strtoupper(uniqid()),
    ]);

    Resend::emails()->send([
        'from' => 'Nuve Express <no-reply@nuveexpress.com.mx>',
        'to' => $request->input('user_info.email'),
        'subject' => 'Reserva con pago en recepción',
        'html' => (new BookingInReceptionCustomerMail($request->all()))->render()
    ]);

    Resend::emails()->send([
        'from' => 'Nuve Express <no-reply@nuveexpress.com.mx>',
        'to' => 'bedbedhoteles@gmail.com',
        'subject' => 'Reserva con pago en recepción',
        'html' => (new BookingInReceptionAdminMail($request->all()))->render()
    ]);

    return response()->json(['message' => 'Reserva creada con éxito para pago en recepción.'], 201);
});

Route::prefix('checkout')->group(function () {
    Route::post('/', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->withoutMiddleware([VerifyCsrfToken::class])->name('stripe.webhook');
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
