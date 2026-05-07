<template>
    <Head title="Disponibilidad de habitaciones" />
    <Header />

    <!-- Hero -->
    <div class="relative h-[500px] w-full bg-[url(/img/home-1.webp)] bg-cover bg-center md:bg-bottom bg-fixed overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/50 to-black/80 pointer-events-none"></div>
        <div class="absolute inset-0 z-10 flex flex-col items-center justify-end p-10 text-white">
            <span class="text-xs font-semibold tracking-widest uppercase text-white/60 mb-2">Nuve Hoteles</span>
            <h1 class="text-3xl font-bold">Reserva tu habitación</h1>
        </div>
    </div>

    <section class="max-w-[1400px] mx-auto px-4 py-8">

        <!-- Countdown -->
        <div v-if="rooms.length || roomsGomez.length" class="flex items-center gap-2 mb-8 w-fit bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse flex-shrink-0"></span>
            <el-countdown
                title="Tiempo restante en la sesión"
                :value="countDown"
                format="mm:ss"
                @finish="onFinish"
                class="text-sm font-medium text-amber-700"
            />
        </div>

        <div v-if="rooms.length || roomsGomez.length" class="flex flex-col md:flex-row gap-10">

            <!-- Nuve Torreón -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Nuve Torreón</h2>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <article
                    v-for="room in rooms"
                    :key="room.code"
                    class="w-full md:max-w-[500px] bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4"
                >
                    <div class="w-full h-[220px]">
                        <el-carousel trigger="click" height="220px" :interval="6000">
                            <template v-for="image in roomImages(room)" :key="image">
                                <el-carousel-item>
                                    <img :src="image" alt="" class="w-full h-full object-cover">
                                </el-carousel-item>
                            </template>
                        </el-carousel>
                    </div>

                    <div class="p-5 flex flex-col gap-4">
                        <!-- Encabezado -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ typeHabs[room.name] || room.name }}
                            </h3>
                            <!-- <span class="inline-block mt-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-0.5">
                                Desayuno incluído por promoción
                            </span> -->
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <!-- Precios -->
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Tarifa por noche desde</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ format_mxn(toCents(firstNightRate(room))) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ gapDate() }} · {{ data.adults }} {{ Number(data.adults) === 1 ? 'adulto' : 'adultos' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <div class="flex items-center gap-1 justify-end">
                                    <span class="text-base font-bold text-gray-900">
                                        Total: {{ format_mxn(totalCents(room), true) }}
                                    </span>
                                    <el-dropdown placement="top-start">
                                        <InfoSvg :width="14" :height="14" class="text-gray-400 cursor-pointer" />
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <div class="flex flex-col px-3 py-1 min-w-[220px]">
                                                    <p class="font-semibold text-center text-sm">Desglose de costos</p>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <div v-for="r in room.rates" :key="r.date" class="flex justify-between gap-2 text-sm">
                                                        <span class="whitespace-nowrap">{{ new Date(r.date).toLocaleDateString('es-MX') }}</span>
                                                        <span class="font-medium">{{ format_mxn(toCents(r.rate)) }}</span>
                                                    </div>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <p class="flex justify-between text-sm">
                                                        <span>Habitaciones</span>
                                                        <span>x {{ data.numHabs }}</span>
                                                    </p>
                                                    <p class="text-right font-semibold text-sm mt-1">{{ format_mxn(totalCents(room), true) }}</p>
                                                    <p class="text-xs text-gray-400">* IVA incluído</p>
                                                </div>
                                            </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <el-button
                                class="w-full nuve-btn"
                                :loading="isLoading"
                                @click="pendingRoom = room; goToCheckout(room);"
                            >
                                Pagar en línea
                            </el-button>
                            <el-button class="w-full mt-2" style="margin-left:0px !important;" type="default" :loading="isLoading"
                                @click="pendingRoom = room; isPayingAtHotel = true; bookingInReception(room);">
                                Pagar en recepción
                            </el-button>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Nuve Gómez -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Nuve Gómez</h2>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <article
                    v-for="room in visibleRoomsGomez"
                    :key="`${room.hotel_code}-${room.code}`"
                    class="w-full md:max-w-[500px] bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4"
                >
                    <div class="w-full h-[220px]">
                        <el-carousel trigger="click" height="220px" :interval="6000">
                            <template v-for="image in roomImages(room)" :key="image">
                                <el-carousel-item>
                                    <img :src="image" alt="" class="w-full h-full object-cover">
                                </el-carousel-item>
                            </template>
                        </el-carousel>
                    </div>

                    <div class="p-5 flex flex-col gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ typeHabs[room.name] || room.name }}
                            </h3>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Tarifa por noche desde</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ format_mxn(toCents(firstNightRate(room))) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ gapDate() }} · {{ data.adults }} {{ Number(data.adults) === 1 ? 'adulto' : 'adultos' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <div class="flex items-center gap-1 justify-end">
                                    <span class="text-base font-bold text-gray-900">
                                        Total: {{ format_mxn(totalCents(room), true) }}
                                    </span>
                                    <el-dropdown placement="top-start">
                                        <InfoSvg :width="14" :height="14" class="text-gray-400 cursor-pointer" />
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <div class="flex flex-col px-3 py-1 min-w-[220px]">
                                                    <p class="font-semibold text-center text-sm">Desglose de costos</p>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <div v-for="r in room.rates" :key="r.date" class="flex justify-between gap-2 text-sm">
                                                        <span class="whitespace-nowrap">{{ new Date(r.date).toLocaleDateString('es-MX') }}</span>
                                                        <span class="font-medium">{{ format_mxn(toCents(r.rate)) }}</span>
                                                    </div>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <p class="flex justify-between text-sm">
                                                        <span>Habitaciones</span>
                                                        <span>x {{ data.numHabs }}</span>
                                                    </p>
                                                    <p class="text-right font-semibold text-sm mt-1">{{ format_mxn(totalCents(room), true) }}</p>
                                                    <p class="text-xs text-gray-400">* IVA incluído</p>
                                                </div>
                                            </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <el-button
                                class="w-full nuve-btn"
                                :loading="isLoading"
                                @click="pendingRoom = room; goToCheckout(room);"
                            >
                                Pagar en línea
                            </el-button>
                            <el-button class="w-full mt-2" style="margin-left:0px !important;" type="default" :loading="isLoading"
                                @click="pendingRoom = room; isPayingAtHotel = true; bookingInReception(room);">
                                Pagar en recepción
                            </el-button>
                        </div>
                    </div>
                </article>
            </div>

        </div>

        <!-- Estado vacío -->
        <div v-else class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <BedSvg :width="24" :height="24" class="text-gray-400" />
            </div>
            <p class="text-gray-500 text-sm max-w-xs">
                Por favor, selecciona fechas válidas para ver las habitaciones disponibles.
            </p>
        </div>

    </section>

    <UserComplementaryData v-model:visible="showUserComplementaryData" @send-data="onReceiveData" :is-paying-at-hotel="false" />
    <Footer class="mt-14" />
</template>

<script>
import BedSvg from '@/Components/BedSvg.vue';
import InfoSvg from '@/Components/InfoSvg.vue';
import WifiSvg from '@/Components/WifiSvg.vue';
import ACSvg from '@/Components/ACSvg.vue';
import Header from '@/Components/Header.vue';
import axios from 'axios';
import { ElNotification } from 'element-plus';
import TVSvg from '@/Components/TVSvg.vue';
import ParkingSvg from '@/Components/ParkingSvg.vue';
import UserComplementaryData from '@/Components/UserComplementaryData.vue';
import { Head, router } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';

export default {
    components: {
        BedSvg,
        InfoSvg,
        WifiSvg,
        ACSvg,
        TVSvg,
        ParkingSvg,
        Header,
        Head,
        UserComplementaryData,
        WhatsappButton,
        Footer,
    },
    props: {
        rooms: { type: Array, default: () => [] }, // viene del backend (tarifas)
        roomsGomez: { type: Array, default: () => [] }, // viene del backend (tarifas)
        data: { type: Object, required: true },   // dateIni, dateFin, numHabs, adults
    },
    data() {
        return {
            countDown: Date.now() + 300 * 1000,
            showUserComplementaryData: false,
            enabledButton: false,
            isLoading: false,
            userInfo: null,
            fullDate: null,
            form: {
                dateIni: "",
                dateFin: "",
                typeHab: "",
                numHabs: null,
                adults: null

            },
            typeHabs: {
                "1K": "King size",
                "1M": "Habitación sencilla",
                "2M": "Habitación doble",
                "DO": "Doble",
                "Q": "Queen",
                "S": "Standard",
                "SK": "Standard King Size",
                "S-": "Sencilla",
                "D-": "Doble",
            },

            imagesRooms: {
                "torreon" : {
                    "1M": [
                        "/img/nuve-torreon-habs/nuve-torreon-sencilla-1.webp",
                        "/img/nuve-torreon-habs/nuve-torreon-sencilla-2.webp",
                        "/img/nuve-torreon-habs/nuve-torreon-sencilla-3.webp",
                        "/img/nuve-torreon-habs/nuve-torreon-sencilla-4.webp",
                    ],
                    "2M": [
                        "/img/nuve-torreon-habs/nuve-torreon-doble-1.webp",
                        "/img/nuve-torreon-habs/nuve-torreon-doble-2.webp",
                    ],
                },
                "gomez" : {
                    "S-": [
                        "/img/nuve-gomez-habs/nuve-gomez-sencilla-1.webp",
                    ],
                    "D-": [
                        "/img/nuve-gomez-habs/nuve-gomez-doble-1.webp",
                        "/img/nuve-gomez-habs/nuve-gomez-doble-2.webp",
                        "/img/nuve-gomez-habs/nuve-gomez-doble-3.webp",
                        "/img/nuve-gomez-habs/nuve-gomez-doble-4.webp",
                    ],
                },
            },

            pendingRoom: null,
            pendingHotelCode: null,
        }
    },
    mounted() {
    },
    computed: {
        infoUser() {
            return this.$page?.props?.auth?.user || null
        },
        isLogged() {
            return !!this.$page?.props?.auth?.check
        },
        visibleRoomsGomez() {
            return (this.roomsGomez || []).filter((room) => ['S-', 'D-'].includes(room?.code));
        },
    },
    methods: {
        disabledBeforeToday(date) {
            const t = new Date(); t.setHours(0, 0, 0, 0)
            return date < t
        },
        disabledBeforeStart(date) {
            const t = new Date(); t.setHours(0, 0, 0, 0)
            const start = this.form.dateIni ? new Date(this.form.dateIni) : t
            start.setHours(0, 0, 0, 0)
            return date < start
        },
        searchHabs() {
            this.isLoading = true;

            // Normaliza daterange: [inicio, fin] -> dateIni=inicio, dateFin=fin
            const range = this.fullDate;

            if (Array.isArray(range) && range.length === 2) {
                const [start, end] = range;
                this.form.dateIni = start;
                this.form.dateFin = end;
            }
            router.post('/disponibilidad', this.form, {
                onError: (errors) => {
                    ElNotification({
                        title: 'Error',
                        message: 'Debes llenar todos los campos del formulario',
                        type: 'error'
                    })
                },
                onFinish: () => {
                    this.isLoading = false;
                }
            })
        },
        nochesTot() {
            const ini = new Date(`${this.data.dateIni}T00:00:00`);
            const fin = new Date(`${this.data.dateFin}T00:00:00`);
            return Math.max(0, Math.floor((fin - ini) / 86400000));
        },
        gapDate() {
            const n = this.nochesTot();
            return `${n} ${n === 1 ? 'noche' : 'noches'}`;
        },
        firstNightRate(room) {
            return Number(room?.rates?.[0]?.rate ?? 0);
        },
        totalCents(room) {
            const nightsSum = (room?.rates || []).reduce((acc, r) => acc + Number(r.rate || 0), 0);
            const total = nightsSum * Number(this.data.numHabs || 1);
            return this.toCents(total);
        },
        toCents(amount) {
            return Math.round(Number(amount || 0) * 100);
        },
        format_mxn(cents, withCode = false) {
            const amount = (cents || 0) / 100;
            const out = new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 2,
            }).format(amount);
            return withCode ? `${out} MXN` : out;
        },
        goToCheckout(room) {
            room = room || this.pendingRoom
            const hotelCode = room?.hotel_code || this.pendingHotelCode
            this.pendingRoom = room
            this.pendingHotelCode = hotelCode

            // Si no hay sesión y tampoco datos del modal → abre modal
            if (!this.isLogged && !this.userInfo) {
                this.showUserComplementaryData = true
                return
            }

            this.isLoading = true

            const nights = this.nochesTot()
            const roomsCount = Number(this.data.numHabs) || 0

            if (!nights || nights < 1) {
                this.isLoading = false
                return ElNotification({
                    title: 'Error',
                    message: 'Rango de fechas inválidas',
                    type: 'error',
                })
            }

            if (!roomsCount || roomsCount < 1) {
                this.isLoading = false
                return ElNotification({
                    title: 'Error',
                    message: 'No hay habitaciones seleccionadas',
                    type: 'error',
                })
            }

            if (!this.data.adults || Number(this.data.adults) < 1) {
                this.isLoading = false
                return ElNotification({
                    title: 'Error',
                    message: 'No se pudo determinar la cantidad de adultos',
                    type: 'error',
                })
            }

            // Total calculado desde las tarifas del room seleccionado
            const amount = this.totalCents(room)
            if (!amount || amount <= 0) {
                this.isLoading = false
                return ElNotification({
                    title: 'Error',
                    message: 'No se pudo calcular el costo total de la reservación',
                    type: 'error',
                })
            }

            const payload = {
                amount,                       // en centavos (MXN)
                currency: 'MXN',
                hotel_code: hotelCode,
                room_type_code: room.code,    // viene del objeto room
                checkin: this.data.dateIni,
                checkout: this.data.dateFin,
                rooms: roomsCount,
                adults: Number(this.data.adults),
                userInfo: this.isLogged ? null : this.userInfo, // sólo manda datos extra si no hay sesión
            }

            axios.post(this.route('checkout.create'), payload)
                .then((response) => {
                    const url = response?.data?.url
                    if (url) window.location.href = url
                    else {
                        ElNotification({
                            title: 'Error',
                            message: 'No se recibió URL de checkout',
                            type: 'error',
                        })
                    }
                })
                .catch(() => {
                    ElNotification({
                        title: 'Error',
                        message: 'Ocurrió un error al ir al formulario de pago',
                        type: 'error',
                    })
                })
                .finally(() => {
                    this.isLoading = false
                })
        },
        onReceiveData(data) {
            this.userInfo = data;
            this.goToCheckout();
        },
        onFinish() {
            window.location.reload();
        },
        normalizeRoomImageCode(code) {
            return String(code || '')
                .toUpperCase()
                .replace(/-/g, '')
                .trim();
        },
        roomImages(room) {
            const hotelCode = room?.hotel_code || this.pendingHotelCode || 'torreon';
            const normalizedCode = this.normalizeRoomImageCode(room?.code);
            const hotelImages = this.imagesRooms[hotelCode] || {};

            if (normalizedCode === '1M') {
                return hotelImages["1M"] || hotelImages["S-"] || [];
            }

            if (normalizedCode === '2M') {
                return hotelImages["2M"] || hotelImages["D-"] || [];
            }

            if (normalizedCode === 'S') {
                return hotelImages["S-"] || hotelImages["1M"] || [];
            }

            if (normalizedCode === 'D') {
                return hotelImages["D-"] || hotelImages["2M"] || [];
            }

            return hotelImages[room?.code]
                || hotelImages[normalizedCode]
                || hotelImages[`${normalizedCode}-`]
                || [];
        }
    },
}
</script>

<style scoped>
.mi-input-custom :deep(.el-input__wrapper) {
    border-color: #6e6e6e;
    /* azul */
    box-shadow: 0 0 0 1px #6e6e6e;
    /* Element Plus usa box-shadow para el borde */
}

.nuve-btn {
  background-color: #1182ba;
  border-color: #1182ba;
  color: white;
}

/* Hover / focus */
.nuve-btn:hover,
.nuve-btn:focus {
  background-color: #0e6f9e;   /* un poco más oscuro */
  border-color: #0e6f9e;
}

/* Disabled (mismo color pero con transparencia) */
.nuve-btn.is-disabled {
  background-color: #1182ba80;
  border-color: #1182ba80;
}
</style>
