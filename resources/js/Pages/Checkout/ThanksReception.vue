<template>
  <div class="bg-gray-50 min-h-screen">
    <Head title="Reserva en Recepción" />
    <Header />

    <main class="min-h-[70vh] grid place-items-center p-6 mt-24">
      <div class="w-full max-w-xl bg-white rounded-2xl shadow-sm p-8 text-center">
        
        <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 class="text-2xl font-bold">¡Reserva registrada!</h1>
        <p class="mt-2 text-gray-600">
          Tu reservación ha sido creada exitosamente. 
          <span class="block font-semibold text-nuve-express-orange">El pago se realizará directamente en recepción.</span>
        </p>

        <div v-if="reservation" class="mt-6 grid grid-cols-1 gap-3 text-sm text-left border-t border-b py-6 border-gray-100">
          
          <div class="flex justify-between">
            <span class="text-gray-500">Cliente:</span>
            <span class="font-semibold">{{ reservation.user_info?.name }} {{ reservation.user_info?.lastname }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Hotel:</span>
            <span class="font-medium">{{ reservation.hotel_name || hotelName(reservation.hotel_code) }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Habitación:</span>
            <span class="font-medium">{{ reservation.room_name }}</span>
          </div>

          <div class="flex justify-between" v-if="reservation.plan">
            <span class="text-gray-500">Plan:</span>
            <span class="font-medium">{{ reservation.plan }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Check-in / Check-out:</span>
            <span class="font-medium">
              {{ formatDate(reservation.check_in) }} — {{ formatDate(reservation.check_out) }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Habitaciones / Adultos:</span>
            <span class="font-medium">
              {{ reservation.num_habs }} / {{ reservation.adults }}
            </span>
          </div>

          <div class="flex justify-between pt-2 border-t border-dashed">
            <span class="text-gray-500 font-bold">Total a pagar:</span>
            <span class="font-bold text-lg text-gray-900">
              {{ formatCurrency(reservation.amount_cents) }}
            </span>
          </div>

          <div class="mt-2 flex justify-between italic text-xs">
            <span class="text-gray-400">Confirmación enviada a:</span>
            <span class="text-gray-500">{{ reservation.user_info?.email }}</span>
          </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <Link href="/"
            class="inline-flex items-center justify-center rounded-lg bg-nuve-express-orange px-8 py-3 font-semibold text-white hover:opacity-90 transition-all">
            Volver al inicio
          </Link>
        </div>

        <p class="mt-6 text-xs text-gray-500">
          Por favor, presenta una identificación oficial al llegar a recepción para completar tu proceso.
        </p>

        <p class="text-xs text-gray-500">
          Si necesitas ayuda, escríbenos al 870-148-3119
        </p>

      </div>
    </main>
  </div>
</template>

<script>
import Header from '@/Components/Header.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
  components: { Header, Head, Link },
  props: {
    // Este es el objeto 'reservation' que envías desde el controlador
    reservation: { type: Object, required: true },
    supportEmail: {
      type: String,
      default: () => (import.meta.env.VITE_SUPPORT_EMAIL || 'soporte@nuvehotel.com'),
    },
  },

  methods: {
    formatCurrency(cents) {
      const amount = Number(cents || 0) / 100
      return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
      }).format(amount)
    },

    formatDate(dateStr) {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      if (isNaN(d)) return String(dateStr)
      // Ajuste de zona horaria para evitar que la fecha "salte" un día atrás
      const userTimezoneOffset = d.getTimezoneOffset() * 60000;
      const correctedDate = new Date(d.getTime() + userTimezoneOffset);
      
      return correctedDate.toLocaleDateString('es-MX', { 
        year: 'numeric', 
        month: 'long', 
        day: '2-digit' 
      })
    },

    hotelName(hotelCode) {
      const names = {
        torreon: 'Nuve Torreón',
        gomez: 'Nuve Gomez',
      }

      return names[hotelCode] || hotelCode || 'Hotel'
    },
  },
}
</script>
