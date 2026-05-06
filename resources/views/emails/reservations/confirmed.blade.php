@component('mail::message')
    # ¡Gracias por tu pago!

    Tu reservación quedó **confirmada**.

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

    **Folio:** {{ $reservation->provider_folio ?: 'No disponible' }}
    **Hotel:** {{ $hotelName }}

    **Habitación:** {{ \App\Services\RoomTypeCatalog::label($reservation->room_type_code) }}

    **Check-in:** {{ $formatDate($checkinValue) }}
    **Check-out:** {{ $formatDate($checkoutValue) }}
    **Noches:** {{ $reservation->nights ?? 'No disponible' }}
    **Habitaciones:** {{ $reservation->rooms ?? 'No disponible' }}
    **Adultos:** {{ $reservation->adults ?? 'No disponible' }}

    **Total pagado:** ${{ $totalLabel }} {{ $currency }}

    @component('mail::button', ['url' => url('/')])
        Visitar sitio
    @endcomponent

    Si necesitas ayuda llámanos: 871-577-9488

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent
