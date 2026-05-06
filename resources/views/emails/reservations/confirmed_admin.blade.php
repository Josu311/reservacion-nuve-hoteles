@component('mail::message')
# Nueva Reservación Pagada

Se confirmó una reservación pagada a través de Stripe. Estos son los datos para seguimiento interno:

@php
    $hotelName = $reservation->hotel_code ?: 'Hotel no especificado';

    try {
        if ($reservation->hotel_code) {
            $hotelName = \App\Services\HotelConfig::name($reservation->hotel_code);
        }
    } catch (\Throwable $e) {
        // Conserva el código del hotel como fallback para no romper el correo.
    }

    $formatDate = static function ($value) {
        if (!$value) {
            return 'No disponible';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return 'No disponible';
        }
    };

    $attributes = $reservation->getAttributes();
    $checkinValue = $attributes['checkin'] ?? null;
    $checkoutValue = $attributes['checkout'] ?? null;
    $totalLabel = $totalMx ?? number_format(((int) ($reservation->amount_cents ?? 0)) / 100, 2);
    $currency = $reservation->currency ?: 'MXN';
@endphp

@component('mail::panel')
### Datos del Huésped
**Nombre:** {{ $reservation->guest_name ?: 'No disponible' }}
**Email:** {{ $reservation->guest_email ?: 'No disponible' }}
**Teléfono:** {{ $reservation->guest_phone ?: 'No disponible' }}
@endcomponent

### Detalles de la Estancia
@component('mail::table')
| Concepto | Información |
| :--- | :--- |
| **Folio** | {{ $reservation->provider_folio ?: 'No disponible' }} |
| **Hotel** | {{ $hotelName }} |
| **Habitación** | {{ \App\Services\RoomTypeCatalog::label($reservation->room_type_code) }} |
| **Check-in** | {{ $formatDate($checkinValue) }} |
| **Check-out** | {{ $formatDate($checkoutValue) }} |
| **Noches** | {{ $reservation->nights ?? 'No disponible' }} |
| **Cantidad** | {{ $reservation->rooms ?? 'No disponible' }} Hab. / {{ $reservation->adults ?? 'No disponible' }} Adultos |
| **Total pagado** | ${{ $totalLabel }} {{ $currency }} |
| **Estado** | {{ $reservation->status ?: 'No disponible' }} |
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
