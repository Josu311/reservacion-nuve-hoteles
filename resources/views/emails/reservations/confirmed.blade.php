@component('mail::message')
    # ¡Gracias por tu pago!

    Tu reservación quedó **confirmada**.

    **Folio:** {{ $reservation->provider_folio }}

    @php
        $room_types = [
            "S-" => "Sencilla",
            "D-" => "Doble"
        ]
    @endphp
    **Habitación:** {{ $room_types[$reservation->room_type_code] ?? $reservation->room_type_code }}

    **Check-in:** {{ \Carbon\Carbon::parse($reservation->checkin)->format('d/m/Y') }}
    **Check-out:** {{ \Carbon\Carbon::parse($reservation->checkout)->format('d/m/Y') }}
    **Noches:** {{ $reservation->nights }}
    **Habitaciones:** {{ $reservation->rooms }}
    **Adultos:** {{ $reservation->adults }}

    **Total pagado:** ${{ $totalMx }} {{ $reservation->currency }}

    @component('mail::button', ['url' => url('/')])
        Visitar sitio
    @endcomponent

    Si necesitas ayuda llámanos: 871-577-9488

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent
