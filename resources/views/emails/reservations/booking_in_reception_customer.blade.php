@component('mail::message')
# ¡Tu solicitud de reserva está en camino! 🏨

Hola **{{ $data['user_info']['name'] }}**, hemos recibido tu solicitud de reservación para pago directamente en recepción. Estamos procesando los detalles con el hotel.

@component('mail::panel')
### 📋 Resumen de tu estancia
**Habitación:** {{ $data['room_name'] }}  
**Check-in:** {{ \Carbon\Carbon::parse($data['check_in'])->format('d/m/Y') }}  
**Check-out:** {{ \Carbon\Carbon::parse($data['check_out'])->format('d/m/Y') }}  

**Detalles adicionales:**
* **Adultos:** {{ $data['adults'] }}
* **Habitaciones:** {{ $data['num_habs'] }}
* **Plan:** Desayuno incluído por promoción
@endcomponent

### 💳 Información Importante
Recuerda que esta modalidad es **Pago en Recepción**. Deberás liquidar el monto total al momento de tu llegada al hotel. 

{{-- @component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
Ir a mi cuenta
@endcomponent --}}

**¿Qué sigue?** Te enviaremos un correo electrónico adicional en cuanto el hotel confirme la disponibilidad de tu habitación.

Si tienes alguna duda, responde a este correo o contáctanos al 870-148-3119.

Atentamente,  
El equipo de Nuve Express
@endcomponent