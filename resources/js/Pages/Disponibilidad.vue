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

        <div v-if="hasResults" class="mb-8 grid gap-4 lg:grid-cols-[1fr_380px]">
            <div
                v-if="automaticPromotion"
                class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                <p class="font-semibold">Promocion automatica</p>
                <p>{{ automaticPromotion.name }} - {{ discountLabel(automaticPromotion) }}</p>
            </div>
            <div v-else></div>

            <form class="border border-gray-200 bg-white px-4 py-3" @submit.prevent="applyCoupon">
                <label class="text-xs font-semibold uppercase text-gray-500">Codigo de descuento</label>
                <div class="mt-2 flex gap-2">
                    <el-input
                        v-model="couponForm.code"
                        placeholder="PRUEBA10"
                        :disabled="couponForm.loading"
                        @keyup.enter="applyCoupon"
                    />
                    <el-button type="primary" :loading="couponForm.loading" @click="applyCoupon">
                        Aplicar
                    </el-button>
                </div>
                <div class="mt-2 min-h-[20px] text-xs">
                    <p v-if="activeCoupon" class="text-emerald-700">
                        Cupon {{ activeCoupon.code }} aplicado: {{ discountLabel(activeCoupon) }}
                        <button type="button" class="ml-2 font-semibold text-gray-500" @click="clearCoupon">Quitar</button>
                    </p>
                    <p v-else-if="couponError" class="text-red-600">{{ couponError }}</p>
                </div>
            </form>
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
                                {{ displayRoomName(group.code, room) }}
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
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        v-if="hasDiscount(room)"
                                        class="text-sm text-gray-400 line-through"
                                    >
                                        {{ formatMxn(totalCents(room), true) }}
                                    </span>
                                    <div class="flex items-center gap-1 justify-end">
                                        <span class="text-base font-bold text-gray-900">
                                            Total: {{ formatMxn(finalTotalCents(room), true) }}
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
                                                    <p class="flex justify-between text-sm">
                                                        <span>Subtotal</span>
                                                        <span>{{ formatMxn(totalCents(room), true) }}</span>
                                                    </p>
                                                    <p v-if="automaticPromotionDiscountCents(room)" class="flex justify-between text-sm text-emerald-700">
                                                        <span>Promocion</span>
                                                        <span>-{{ formatMxn(automaticPromotionDiscountCents(room), true) }}</span>
                                                    </p>
                                                    <p v-if="discountCents(room)" class="flex justify-between text-sm text-emerald-700">
                                                        <span>Cupon</span>
                                                        <span>-{{ formatMxn(discountCents(room), true) }}</span>
                                                    </p>
                                                    <p class="text-right font-semibold text-sm mt-1">{{ formatMxn(finalTotalCents(room), true) }}</p>
                                                    <p class="text-xs text-gray-400">* IVA incluído</p>
                                                </div>
                                            </el-dropdown-menu>
                                        </template>
                                        </el-dropdown>
                                    </div>
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
                                v-if="!useParrasBranding"
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

    <!-- Sección de "¿Buscas una opción más accesible?" -->
    <section
        class="relative isolate flex min-h-[440px] w-full items-center justify-center overflow-hidden bg-cover bg-center px-4 py-12 font-sans text-white sm:min-h-[500px] sm:px-6 lg:min-h-[560px]"
        style="background-image: url('/img/buscas-una-opcion-mas-accesible.webp')"
    >
        <div class="absolute inset-0 -z-10 bg-black/65"></div>
        <div class="absolute inset-y-0 left-0 -z-10 w-1/3 bg-gradient-to-r from-black/55 to-transparent"></div>
        <div class="absolute inset-y-0 right-0 -z-10 w-1/3 bg-gradient-to-l from-black/45 to-transparent"></div>

        <div class="mx-auto flex w-full max-w-4xl flex-col items-center text-center">
            <img
                src="/img/Logo-Nuve-Express-01-1.png"
                alt="Nuve Express"
                class="mb-6 h-20 w-auto brightness-0 invert sm:h-24"
            >

            <h2 class="max-w-4xl text-4xl font-black uppercase leading-[0.95] tracking-normal sm:text-5xl lg:text-6xl">
                ¿Buscas una opción<br class="hidden sm:block">
                <span class="block">más accesible?</span>
            </h2>

            <p class="mt-6 text-xl font-medium uppercase leading-tight tracking-normal sm:text-2xl">
                Reserva en
                <span class="font-extrabold italic text-nuve-express-orange">Hotel Nuve Express</span>
            </p>

            <div class="mt-4 uppercase italic leading-none">
                <p class="text-lg font-semibold sm:text-xl">Desde:</p>
                <div class="mt-1 flex flex-wrap items-end justify-center gap-x-3 gap-y-1">
                    <span class="text-5xl font-black leading-none sm:text-6xl lg:text-7xl">$ 950</span>
                    <div class="pb-1 text-left">
                        <span class="block text-3xl font-black sm:text-4xl">MXN</span>
                        <span class="block text-base font-medium sm:text-lg">por noche</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex w-full max-w-lg flex-col justify-center gap-4 sm:flex-row">
                <a
                    href="https://nuveexpress.com.mx/"
                    class="inline-flex min-h-[56px] flex-1 items-center justify-center gap-3 bg-nuve-express-orange px-8 text-base font-bold text-white transition hover:bg-nuve-express-orange/90 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black"
                >
                    Reservar Ahora
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

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
        automaticPromotion: { type: Object, default: null },
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
            activeCoupon: null,
            couponError: null,
            couponForm: {
                code: '',
                loading: false,
            },
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
        applyCoupon() {
            const code = String(this.couponForm.code || '').trim();

            if (!code) {
                this.activeCoupon = null;
                this.couponError = 'Ingresa un codigo de descuento';
                return;
            }

            this.couponForm.loading = true;
            this.couponError = null;

            axios.post(this.route('disponibilidad.coupon.validate'), {
                coupon_code: code,
            })
                .then((response) => {
                    this.activeCoupon = response.data;
                    this.couponForm.code = response.data?.code || code.toUpperCase();
                })
                .catch((error) => {
                    this.activeCoupon = null;
                    this.couponError = error?.response?.data?.message
                        || error?.response?.data?.errors?.coupon_code?.[0]
                        || 'El codigo de descuento no es valido';
                })
                .finally(() => {
                    this.couponForm.loading = false;
                });
        },
        automaticPromotionAppliesToRoom(room) {
            if (!this.automaticPromotion) {
                return false;
            }

            const promoHotel = this.automaticPromotion.hotel_code;
            const promoRoom = this.automaticPromotion.room_type_code;

            if (promoHotel && promoHotel !== room?.hotel_code) {
                return false;
            }

            if (promoRoom && String(promoRoom).toUpperCase() !== String(room?.code || '').toUpperCase()) {
                return false;
            }

            return true;
        },
        automaticPromotionDiscountCents(room) {
            if (!this.automaticPromotionAppliesToRoom(room)) {
                return 0;
            }

            return this.calculateDiscountCents(this.automaticPromotion, this.totalCents(room));
        },
        calculateDiscountCents(discount, subtotalCents) {
            const subtotal = Math.max(0, Number(subtotalCents || 0));
            const value = Number(discount?.discount_value || 0);

            if (!discount || !subtotal || value <= 0) {
                return 0;
            }

            const rawDiscount = discount.discount_type === 'percentage'
                ? Math.round(subtotal * (value / 100))
                : Math.round(value * 100);

            return Math.max(0, Math.min(subtotal, rawDiscount));
        },
        clearCoupon() {
            this.activeCoupon = null;
            this.couponError = null;
            this.couponForm.code = '';
        },
        discountCents(room) {
            if (!this.activeCoupon) {
                return 0;
            }

            return this.calculateDiscountCents(this.activeCoupon, this.subtotalAfterAutomaticPromotionCents(room));
        },
        discountLabel(discount) {
            if (!discount) {
                return '';
            }

            const value = Number(discount.discount_value || 0);

            if (discount.discount_type === 'percentage') {
                return `${value}% de descuento`;
            }

            return `${this.formatMxn(Math.round(value * 100), true)} de descuento`;
        },
        filterRooms(hotelCode, rooms) {
            const allowedCodes = this.roomFilters[hotelCode];

            if (!allowedCodes || allowedCodes.length === 0) {
                return rooms;
            }

            return rooms.filter((room) => allowedCodes.includes(room?.code));
        },
        displayRoomName(room) {
            const hotelCode = room?.hotel_code || '';
            const roomCode = String(room?.code || '').toUpperCase();

            if (hotelCode === 'torreon') {
                const torreonRoomNames = {
                    '1M': 'SENCILLA',
                    '2M': 'DOBLE',
                };

                return torreonRoomNames[roomCode] || room?.name || room?.code;
            }

            return room?.name || room?.code;
        },
        firstNightRate(room) {
            return Number(room?.rates?.[0]?.rate ?? 0);
        },
        finalTotalCents(room) {
            return Math.max(0, this.subtotalAfterAutomaticPromotionCents(room) - this.discountCents(room));
        },
        hasDiscount(room) {
            return this.totalDiscountCents(room) > 0 && this.finalTotalCents(room) < this.totalCents(room);
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
                coupon_code: this.activeCoupon?.code || null,
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
            const roomDisplayName = this.displayRoomName(room.hotel_code, room);

            axios.post('/create-booking-reception', {
                hotel_code: room.hotel_code,
                room_code: room.code,
                room_name: roomDisplayName,
                plan: room.plan,
                check_in: this.data.dateIni,
                check_out: this.data.dateFin,
                adults: Number(this.data.adults),
                num_habs: Number(this.data.numHabs),
                subtotal_cents: this.totalCents(room),
                amount_cents: this.finalTotalCents(room),
                amount: this.finalTotalCents(room) / 100,
                coupon_code: this.activeCoupon?.code || null,
                user_info: this.userInfo,
            })
                .then(() => {
                    window.location.href = `/checkout/success/reception?hotel_code=${encodeURIComponent(room.hotel_code)}&hotel_name=${encodeURIComponent(room.hotel_name || '')}&room_name=${encodeURIComponent(roomDisplayName || '')}&check_in=${encodeURIComponent(this.data.dateIni)}&check_out=${encodeURIComponent(this.data.dateFin)}&adults=${encodeURIComponent(this.data.adults)}&num_habs=${encodeURIComponent(this.data.numHabs)}`;
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
        subtotalAfterAutomaticPromotionCents(room) {
            return Math.max(0, this.totalCents(room) - this.automaticPromotionDiscountCents(room));
        },
        totalDiscountCents(room) {
            return this.automaticPromotionDiscountCents(room) + this.discountCents(room);
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
