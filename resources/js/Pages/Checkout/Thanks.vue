<template>
  <div class="bg-gray-50 min-h-screen">
    <Head title="Estado de tu reservación" />
    <Header />

    <main class="min-h-[70vh] grid place-items-center p-6 mt-24">
      <div class="w-full max-w-xl bg-white rounded-2xl shadow-sm p-8 text-center">

        <!-- ICONO + TÍTULO SEGÚN ESTADO -->
        <div
          class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full"
          :class="isFinal && reservationStatus === 'paid' ? 'bg-green-100' : (reservationStatus === 'failed' || reservationStatus === 'expired' ? 'bg-red-100' : 'bg-yellow-100')"
        >
          <!-- check -->
          <svg v-if="isFinal && reservationStatus === 'paid'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>

          <!-- warning -->
          <svg v-else-if="!isFinal" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-yellow-600" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.02 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3.14l-8.02-14a2 2 0 0 0-3.46 0z" />
          </svg>

          <!-- error -->
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <h1 class="text-2xl font-bold">
          <span v-if="reservationStatus === 'paid'">¡Reserva confirmada!</span>
          <span v-else-if="reservationStatus === 'failed'">No se pudo confirmar la reserva</span>
          <span v-else-if="reservationStatus === 'expired'">La reservación expiró</span>
          <span v-else>Estamos confirmando tu reservación…</span>
        </h1>

        <p class="mt-2 text-gray-600">
          <span v-if="reservationStatus === 'paid'">
            Tu reservación quedó <span class="font-semibold text-green-700">confirmada</span>.
          </span>

          <span v-else-if="reservationStatus === 'failed' && failReason === 'no_availability'">
            Ya no hubo disponibilidad. <span class="font-semibold">No se realizó el cobro</span>.
          </span>

          <span v-else-if="reservationStatus === 'failed'">
            Ocurrió un problema al procesar tu pago o confirmar con el proveedor.
          </span>

          <span v-else-if="reservationStatus === 'expired'">
            El tiempo de espera para completar la compra terminó.
          </span>

          <span v-else>
            Esto puede tardar unos segundos. No cierres esta página.
          </span>
        </p>

        <!-- RESUMEN (solo si ya existe currentReservation) -->
        <div v-if="currentReservation" class="mt-6 grid grid-cols-1 gap-3 text-sm text-left">
          <div class="flex justify-between">
            <span class="text-gray-500">Folio proveedor:</span>
            <span class="font-semibold">{{ currentReservation.provider_folio }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Hotel:</span>
            <span class="font-medium">{{ hotelName(currentReservation.hotel_code) }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Habitación:</span>
            <span class="font-medium">{{ typeHabs[currentReservation.room_type_code] || currentReservation.room_type_code }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Check-in / Check-out:</span>
            <span class="font-medium">
              {{ formatDate(currentReservation.checkin) }} — {{ formatDate(currentReservation.checkout) }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Noches / Habitaciones / Adultos:</span>
            <span class="font-medium">
              {{ currentReservation.nights }} / {{ currentReservation.rooms }} / {{ currentReservation.adults }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Total:</span>
            <span class="font-semibold">
              {{ formatCurrency(currentReservation.amount_cents, currentReservation.currency) }}
            </span>
          </div>

          <div v-if="contactEmail" class="flex justify-between">
            <span class="text-gray-500">Correo de contacto:</span>
            <span class="font-medium">{{ contactEmail }}</span>
          </div>
        </div>

        <!-- BOTONES -->
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <Link href="/"
            class="inline-flex items-center justify-center rounded-lg bg-nuve-express-orange px-5 py-2.5 font-semibold text-white hover:opacity-90">
            Ir al inicio
          </Link>

          <!-- Si quieres el comprobante, mejor usa Stripe customer portal o correo.
               stripe_checkout_url no es “comprobante” y puede ya no servir después. -->
        </div>

        <p class="mt-6 p-0 text-xs text-gray-500">
          <span v-if="reservationStatus === 'paid'">Los datos de tu reservación fueron enviados por correo.</span>
          <span v-else>Te avisaremos por correo cuando esté confirmada.</span>
        </p>

        <p class="text-xs text-gray-500">
          Si necesitas ayuda, escríbenos a
          <a :href="`mailto:${supportEmail}`" class="underline">{{ supportEmail }}</a>.
        </p>

      </div>
    </main>
  </div>
</template>


<script>
import Header from '@/Components/Header.vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  components: { Header, Head, Link },
  props: {
    // Ahora puede venir null mientras el webhook procesa
    reservation: { type: Object, default: null },
    sessionId: { type: String, default: '' },

    // Ya no lo usaremos como “verdad”, pero lo dejo por compatibilidad
    idTX: { type: String, default: '' },

    supportEmail: {
      type: String,
      default: () => (import.meta.env.VITE_SUPPORT_EMAIL || 'soporte@nuvehotel.com'),
    },
  },

  data() {
    return {
      typeHabs: {
        "1K": "King Size",
        "DO": "Doble",
        "MK": "Queen",
        "SD": "Standard",
        "SK": "Standard King Size",
        "S-": "Sencilla",
        "D-": "Doble",
      },

      // Estado dinámico (se actualiza por polling)
      currentReservation: this.reservation,
      poller: null,
      pollingState: 'idle', // idle | polling | done | error
      failReason: null,
    }
  },

  computed: {
    contactEmail() {
      const r = this.currentReservation
      return (r?.user?.email) || r?.guest_email || ''
    },

    reservationStatus() {
      return this.currentReservation?.status || 'awaiting_payment'
    },

    // Si quieres usarlo en el template para decidir mensaje
    isFinal() {
      return ['paid', 'failed', 'expired', 'cancelled'].includes(this.reservationStatus)
    },
  },

  mounted() {
    // Si no tenemos sessionId, no podemos consultar status
    if (!this.sessionId) return

    // Arranca polling inmediatamente
    this.pollingState = 'polling'
    this.fetchStatus()

    this.poller = setInterval(() => {
      this.fetchStatus()
    }, 2500)
  },

  beforeUnmount() {
    if (this.poller) {
      clearInterval(this.poller)
      this.poller = null
    }
  },

  methods: {
    async fetchStatus() {
      try {
        const res = await axios.get('/checkout/status', {
          params: { session_id: this.sessionId },
          headers: { 'Cache-Control': 'no-cache' },
        })

        if (res?.data?.reservation) {
          // merge para no perder campos que ya tenías
          this.currentReservation = {
            ...(this.currentReservation || {}),
            ...res.data.reservation,
          }
        }

        this.failReason = res?.data?.fail_reason || null

        if (res?.data?.is_final) {
          this.pollingState = 'done'
          if (this.poller) {
            clearInterval(this.poller)
            this.poller = null
          }
        }
      } catch (e) {
        // No cierres de inmediato: un 404 temporal puede pasar si aún no guardaste session_id
        // pero si quieres, puedes cortar después de N errores.
        this.pollingState = 'error'
      }
    },

    formatCurrency(cents, currency = 'MXN') {
      const amount = Number(cents || 0) / 100
      return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency || 'MXN',
        minimumFractionDigits: 2,
      }).format(amount)
    },

    formatDate(dateStr) {
      const d = new Date(dateStr)
      if (isNaN(d)) return String(dateStr || '')
      return d.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' })
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
