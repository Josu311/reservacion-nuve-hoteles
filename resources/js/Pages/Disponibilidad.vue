<template>
    <Head :title="pageTitle" />
    <HeaderParras v-if="useParrasBranding" />
    <Header v-else />

    <div
        class="relative h-[500px] w-full bg-cover bg-center md:bg-bottom bg-fixed overflow-hidden"
        :style="{ backgroundImage: `url(${heroImage})` }"
    >
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/50 to-black/80 pointer-events-none"></div>
        <div class="absolute inset-0 z-10 flex flex-col items-center justify-end p-10 text-white">
            <span class="text-xs font-semibold tracking-widest uppercase text-white/60 mb-2">{{ heroBadge }}</span>
            <h1 class="text-3xl font-bold text-center">{{ heroTitle }}</h1>
        </div>
    </div>

    <section class="max-w-[1400px] mx-auto px-4 py-8">
        <div v-if="hasResults" class="flex items-center gap-2 mb-8 w-fit bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse flex-shrink-0"></span>
            <el-countdown
                title="Tiempo restante en la sesión"
                :value="countDown"
                format="mm:ss"
                @finish="onFinish"
                class="text-sm font-medium text-amber-700"
            />
        </div>

        <div
            v-if="hasResults"
            class="grid gap-10"
            :class="isSingleHotel ? 'grid-cols-1 max-w-3xl' : 'grid-cols-1 lg:grid-cols-2'"
        >
            <div v-for="group in visibleHotelGroups" :key="group.code" class="min-w-0">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-xl font-bold text-gray-900">{{ group.name }}</h2>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <article
                    v-for="room in group.visibleRooms"
                    :key="`${group.code}-${room.code}`"
                    class="w-full bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4"
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
                                {{ room.name || room.code }}
                            </h3>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Tarifa por noche desde</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ formatMxn(toCents(firstNightRate(room))) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ gapDate() }} · {{ data.adults }} {{ Number(data.adults) === 1 ? 'adulto' : 'adultos' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <div class="flex items-center gap-1 justify-end">
                                    <span class="text-base font-bold text-gray-900">
                                        Total: {{ formatMxn(totalCents(room), true) }}
                                    </span>
                                    <el-dropdown placement="top-start">
                                        <InfoSvg :width="14" :height="14" class="text-gray-400 cursor-pointer" />
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <div class="flex flex-col px-3 py-1 min-w-[220px]">
                                                    <p class="font-semibold text-center text-sm">Desglose de costos</p>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <div v-for="rate in room.rates" :key="rate.date" class="flex justify-between gap-2 text-sm">
                                                        <span class="whitespace-nowrap">{{ new Date(rate.date).toLocaleDateString('es-MX') }}</span>
                                                        <span class="font-medium">{{ formatMxn(toCents(rate.rate)) }}</span>
                                                    </div>
                                                    <div class="h-px w-full bg-gray-200 my-2"></div>
                                                    <p class="flex justify-between text-sm">
                                                        <span>Habitaciones</span>
                                                        <span>x {{ data.numHabs }}</span>
                                                    </p>
                                                    <p class="text-right font-semibold text-sm mt-1">{{ formatMxn(totalCents(room), true) }}</p>
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
                                @click="startCheckout(room)"
                            >
                                Pagar en línea
                            </el-button>
                            <el-button
                                class="w-full mt-2"
                                style="margin-left:0px !important;"
                                type="default"
                                :loading="isLoading"
                                @click="startReceptionBooking(room)"
                            >
                                Pagar en recepción
                            </el-button>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-24 text-center">
            <div
                class="w-14 h-14 rounded-full flex items-center justify-center mb-4"
                :class="availabilityError ? 'bg-red-50' : 'bg-gray-100'"
            >
                <BedSvg
                    :width="24"
                    :height="24"
                    :class="availabilityError ? 'text-red-400' : 'text-gray-400'"
                />
            </div>
            <p
                class="text-sm max-w-xs"
                :class="availabilityError ? 'text-red-600' : 'text-gray-500'"
            >
                {{ availabilityError || 'Por favor, selecciona fechas válidas para ver las habitaciones disponibles.' }}
            </p>
        </div>
    </section>

    <UserComplementaryData
        v-model:visible="showUserComplementaryData"
        @send-data="onReceiveData"
        :is-paying-at-hotel="isPayingAtHotel"
    />
    <Footer
        class="mt-14"
        :logo-src="useParrasBranding ? '/img/parras/hotel-parras-logo.webp' : '/img/logo-nuve-hoteles.webp'"
    />
</template>

<script>
import BedSvg from '@/Components/BedSvg.vue';
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import InfoSvg from '@/Components/InfoSvg.vue';
import HeaderParras from '@/Components/Parras/HeaderParras.vue';
import UserComplementaryData from '@/Components/UserComplementaryData.vue';
import axios from 'axios';
import { ElNotification } from 'element-plus';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        BedSvg,
        Footer,
        Head,
        Header,
        HeaderParras,
        InfoSvg,
        UserComplementaryData,
    },
    props: {
        data: { type: Object, required: true },
        availabilityError: { type: String, default: null },
        heroBadge: { type: String, default: 'Nuve Hoteles' },
        heroImage: { type: String, default: '/img/home-1.webp' },
        heroTitle: { type: String, default: 'Reserva tu habitación' },
        hotelGroups: { type: Array, default: () => [] },
        isSingleHotel: { type: Boolean, default: false },
        pageTitle: { type: String, default: 'Disponibilidad de habitaciones' },
        searchPath: { type: String, default: '/disponibilidad' },
        useParrasBranding: { type: Boolean, default: false },
    },
    data() {
        return {
            countDown: Date.now() + 300 * 1000,
            isLoading: false,
            isPayingAtHotel: false,
            pendingRoom: null,
            showUserComplementaryData: false,
            userInfo: null,
            typeHabs: {
                '1K': 'King size',
                '1M': 'Habitación sencilla',
                '1Q': 'Habitación sencilla king',
                '2M': 'Habitación doble',
                '2Q': 'Habitación doble queen',
                'DO': 'Doble',
                'Q': 'Queen',
                'S': 'Standard',
                'SK': 'Standard King Size',
                'S-': 'Sencilla',
                'D-': 'Doble',
            },
            roomFilters: {
                gomez: ['S-', 'D-'],
                parras: ['1Q', '2Q'],
            },
            imagesRooms: {
                torreon: {
                    '1M': [
                        '/img/nuve-torreon-habs/nuve-torreon-sencilla-1.webp',
                        '/img/nuve-torreon-habs/nuve-torreon-sencilla-2.webp',
                        '/img/nuve-torreon-habs/nuve-torreon-sencilla-3.webp',
                        '/img/nuve-torreon-habs/nuve-torreon-sencilla-4.webp',
                    ],
                    '2M': [
                        '/img/nuve-torreon-habs/nuve-torreon-doble-1.webp',
                        '/img/nuve-torreon-habs/nuve-torreon-doble-2.webp',
                    ],
                },
                gomez: {
                    'S-': ['/img/nuve-gomez-habs/nuve-gomez-sencilla-1.webp'],
                    'D-': [
                        '/img/nuve-gomez-habs/nuve-gomez-doble-1.webp',
                        '/img/nuve-gomez-habs/nuve-gomez-doble-2.webp',
                        '/img/nuve-gomez-habs/nuve-gomez-doble-3.webp',
                        '/img/nuve-gomez-habs/nuve-gomez-doble-4.webp',
                    ],
                },
                parras: {
                    '2Q': [
                        '/img/nuve-parras-habs/dobles-queen/hotels-19.webp',
                        '/img/nuve-parras-habs/dobles-queen/hotels-23.jpg',
                        '/img/nuve-parras-habs/dobles-queen/hotels-25.webp',
                    ],
                    '2M': [
                        '/img/nuve-parras-habs/dobles-matrimoniales/hotels-35.webp',
                    ],
                    '1Q': [
                        '/img/nuve-parras-habs/sencillas-king/hotels-21.webp',
                        '/img/nuve-parras-habs/sencillas-king/hotels-24.webp',
                    ],
                },
            },
        };
    },
    computed: {
        hasResults() {
            return this.visibleHotelGroups.some((group) => group.visibleRooms.length > 0);
        },
        infoUser() {
            return this.$page?.props?.auth?.user || null;
        },
        isLogged() {
            return !!this.$page?.props?.auth?.check;
        },
        visibleHotelGroups() {
            return (this.hotelGroups || [])
                .map((group) => ({
                    ...group,
                    visibleRooms: this.filterRooms(group.code, group.rooms || []),
                }))
                .filter((group) => group.visibleRooms.length > 0);
        },
    },
    methods: {
        filterRooms(hotelCode, rooms) {
            const allowedCodes = this.roomFilters[hotelCode];

            if (!allowedCodes || allowedCodes.length === 0) {
                return rooms;
            }

            return rooms.filter((room) => allowedCodes.includes(room?.code));
        },
        firstNightRate(room) {
            return Number(room?.rates?.[0]?.rate ?? 0);
        },
        formatMxn(cents, withCode = false) {
            const amount = (cents || 0) / 100;
            const output = new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 2,
            }).format(amount);

            return withCode ? `${output} MXN` : output;
        },
        gapDate() {
            const nights = this.nochesTot();
            return `${nights} ${nights === 1 ? 'noche' : 'noches'}`;
        },
        normalizeRoomImageCode(code) {
            return String(code || '')
                .toUpperCase()
                .replace(/-/g, '')
                .trim();
        },
        nochesTot() {
            const checkIn = new Date(`${this.data.dateIni}T00:00:00`);
            const checkOut = new Date(`${this.data.dateFin}T00:00:00`);
            return Math.max(0, Math.floor((checkOut - checkIn) / 86400000));
        },
        onFinish() {
            window.location.reload();
        },
        onReceiveData(data) {
            this.userInfo = data;

            if (this.isPayingAtHotel) {
                this.bookingInReception();
                return;
            }

            this.goToCheckout();
        },
        roomImages(room) {
            const hotelCode = room?.hotel_code || 'torreon';
            const normalizedCode = this.normalizeRoomImageCode(room?.code);
            const hotelImages = this.imagesRooms[hotelCode] || {};

            if (normalizedCode === '1M') {
                return hotelImages['1M'] || hotelImages['S-'] || room?.images || [];
            }

            if (normalizedCode === '2M') {
                return hotelImages['2M'] || hotelImages['D-'] || room?.images || [];
            }

            if (normalizedCode === 'S') {
                return hotelImages['S-'] || hotelImages['1M'] || room?.images || [];
            }

            if (normalizedCode === 'D') {
                return hotelImages['D-'] || hotelImages['2M'] || room?.images || [];
            }

            return hotelImages[room?.code]
                || hotelImages[normalizedCode]
                || hotelImages[`${normalizedCode}-`]
                || room?.images
                || [];
        },
        startCheckout(room) {
            this.isPayingAtHotel = false;
            this.pendingRoom = room;
            this.goToCheckout(room);
        },
        startReceptionBooking(room) {
            this.isPayingAtHotel = true;
            this.pendingRoom = room;

            if (!this.userInfo) {
                this.showUserComplementaryData = true;
                return;
            }

            this.bookingInReception(room);
        },
        goToCheckout(room) {
            room = room || this.pendingRoom;
            this.pendingRoom = room;

            if (!this.isLogged && !this.userInfo) {
                this.showUserComplementaryData = true;
                return;
            }

            this.isLoading = true;

            const nights = this.nochesTot();
            const roomsCount = Number(this.data.numHabs) || 0;

            if (!nights || nights < 1) {
                this.isLoading = false;
                return ElNotification({
                    title: 'Error',
                    message: 'Rango de fechas inválidas',
                    type: 'error',
                });
            }

            if (!roomsCount || roomsCount < 1) {
                this.isLoading = false;
                return ElNotification({
                    title: 'Error',
                    message: 'No hay habitaciones seleccionadas',
                    type: 'error',
                });
            }

            if (!this.data.adults || Number(this.data.adults) < 1) {
                this.isLoading = false;
                return ElNotification({
                    title: 'Error',
                    message: 'No se pudo determinar la cantidad de adultos',
                    type: 'error',
                });
            }

            const amount = this.totalCents(room);
            if (!amount || amount <= 0) {
                this.isLoading = false;
                return ElNotification({
                    title: 'Error',
                    message: 'No se pudo calcular el costo total de la reservación',
                    type: 'error',
                });
            }

            const payload = {
                amount,
                currency: 'MXN',
                hotel_code: room.hotel_code,
                room_type_code: room.code,
                checkin: this.data.dateIni,
                checkout: this.data.dateFin,
                rooms: roomsCount,
                adults: Number(this.data.adults),
                userInfo: this.isLogged ? null : this.userInfo,
            };

            axios.post(this.route('checkout.create'), payload)
                .then((response) => {
                    const url = response?.data?.url;
                    if (url) {
                        window.location.href = url;
                        return;
                    }

                    ElNotification({
                        title: 'Error',
                        message: 'No se recibió URL de checkout',
                        type: 'error',
                    });
                })
                .catch(() => {
                    ElNotification({
                        title: 'Error',
                        message: 'Ocurrió un error al ir al formulario de pago',
                        type: 'error',
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        bookingInReception(room) {
            room = room || this.pendingRoom;
            this.pendingRoom = room;

            if (!this.userInfo) {
                this.showUserComplementaryData = true;
                return;
            }

            this.isLoading = true;

            axios.post('/create-booking-reception', {
                hotel_code: room.hotel_code,
                room_code: room.code,
                room_name: room.name,
                plan: room.plan,
                check_in: this.data.dateIni,
                check_out: this.data.dateFin,
                adults: Number(this.data.adults),
                num_habs: Number(this.data.numHabs),
                amount_cents: this.totalCents(room),
                amount: this.totalCents(room) / 100,
                user_info: this.userInfo,
            })
                .then(() => {
                    window.location.href = `/checkout/success/reception?hotel_code=${encodeURIComponent(room.hotel_code)}&hotel_name=${encodeURIComponent(room.hotel_name || '')}&room_name=${encodeURIComponent(room.name || '')}&check_in=${encodeURIComponent(this.data.dateIni)}&check_out=${encodeURIComponent(this.data.dateFin)}&adults=${encodeURIComponent(this.data.adults)}&num_habs=${encodeURIComponent(this.data.numHabs)}`;
                })
                .catch(() => {
                    ElNotification({
                        title: 'Error',
                        message: 'No se pudo registrar la reserva para pago en recepción',
                        type: 'error',
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        toCents(amount) {
            return Math.round(Number(amount || 0) * 100);
        },
        totalCents(room) {
            const nightsSum = (room?.rates || []).reduce((carry, rate) => carry + Number(rate.rate || 0), 0);
            const total = nightsSum * Number(this.data.numHabs || 1);
            return this.toCents(total);
        },
    },
};
</script>

<style scoped>
.nuve-btn {
    background-color: #1182ba;
    border-color: #1182ba;
    color: white;
}

.nuve-btn:hover,
.nuve-btn:focus {
    background-color: #0e6f9e;
    border-color: #0e6f9e;
}

.nuve-btn.is-disabled {
    background-color: #1182ba80;
    border-color: #1182ba80;
}
</style>
