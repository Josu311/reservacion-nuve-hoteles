@component('mail::message')
# Nueva Reservación para Pago en Recepción

Se ha recibido una solicitud de reserva a través del sistema. Estos son los detalles para su registro:

@component('mail::panel')
### Datos del Huésped
**Nombre:** {{ $data['user_info']['name'] }} {{ $data['user_info']['lastname'] }}
**Email:** {{ $data['user_info']['email'] }}
**Teléfono:** {{ $data['user_info']['phone'] }}
@endcomponent

### Detalles de la Estancia
@component('mail::table')
| Concepto | Información |
| :--- | :--- |
| **Hotel** | {{ $data['hotel_name'] ?? $data['hotel_code'] ?? 'Nuve Express' }} |
| **Habitación** | {{ $data['room_name'] }} |
| **Plan** | Desayuno incluído por promoción |
| **Check-in** | {{ \Carbon\Carbon::parse($data['check_in'])->format('d/m/Y') }} |
| **Check-out** | {{ \Carbon\Carbon::parse($data['check_out'])->format('d/m/Y') }} |
| **Cantidad** | {{ $data['num_habs'] }} Hab. / {{ $data['adults'] }} Adultos |
@endcomponent

{{-- @component('mail::button', ['url' => config('app.url'), 'color' => 'success'])
Ver en el Sistema
@endcomponent --}}

*Nota: Esta reservación está pendiente de confirmación manual en recepción.*

Gracias,<br>
{{ config('app.name') }}
@endcomponent
