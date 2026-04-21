<template>

    <Head title="Disponibilidad de habitaciones" />
    <Header />


    <div class=" relative h-[600px] w-full bg-[url(/img/home-1.webp)] bg-cover bg-center md:bg-bottom bg-fixed">
        <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
        <div class="flex items-center justify-center absolute inset-0 z-10 p-6 text-white text-2xl font-bold">
            Reserva aquí
        </div>
    </div>
    <!-- Un card por cada habitación -->
    <section class="relative max-w-[1600px] h-full mx-auto flex flex-col md:flex-row justify-center gap-5 md:gap-0 mt-3">
        <div class="md:sticky top-20 h-fit flex flex-col gap-5">
            <!-- <div class="relative max-w-96 block h-fit rounded-2xl border border-gray-300 shadow-md p-2 md:p-5 md:mr-5">
                <el-form class="w-full mt-4" :model="form">
                    <div class="flex flex-col gap-2">
                        <h2 class="font-semibold uppercase text-lg">¿Tienes un cupón?</h2>
                        <div class="flex items-center gap-2">
                            <el-form-item class="flex-1 mi-input-custom" style="margin: 0;">
                                <el-input placeholder="Ingresa tu cupón" style="width: 100%;" />
                            </el-form-item>
                            <el-button :disabled="true" type="warning">Buscar</el-button>
                        </div>
                    </div>
                </el-form>
            </div> -->
        </div>
        <div class="p-2 md:p-5">
            <el-countdown v-if="rooms.length || roomsGomez.length" title="Tiempo restante en la sesión" :value="countDown" format="mm:ss" @finish="onFinish" />
            
            <div v-if="rooms.length || roomsGomez.length" class="flex flex-col md:flex-row gap-10">
                <div>
                    <span class="text-2xl text-gray-900 text-center font-bold">Nuve Torreón.</span>
                    <article v-for="room in rooms" :key="room.code"
                        class="w-full md:w-[500px] flex flex-col gap-4 outline outline-1 outline-gray-300 rounded-lg p-3 mt-3">
                        <div class="w-full h-[200px] rounded-md overflow-hidden">
                            <!-- Imagen fija del diseño original -->
                            <el-carousel trigger="click" height="200px" :interval=6000>
                                <template v-for="image in imagesRooms['corregidora'][room.code]" :key="image">
                                    <el-carousel-item>
                                        <img :src="image" alt="" class="w-full h-full object-cover">
                                    </el-carousel-item>
                                </template>
                            </el-carousel>
                        </div>
        
                        <div class="w-full flex flex-col gap-3">
                            <header class="flex flex-col">
                                <h3 class="font-extrabold text-2xl text-gray-800">
                                    {{ room.name || typeHabs[room.code] }}
                                </h3>
                                <span class="w-fit text-gray-400 text-xs font-semibold">
                                    <!-- {{ room.plan || 'Plan no especificado' }} -->
                                      Desayuno incluído por promoción
                                </span>
                            </header>
        
                            <div class="flex-1">
                                <div class="w-full flex justify-between gap-3 text-sm text-gray-700">
                                    <div class="flex flex-col">
                                        <h6 class="font-semibold text-base">Tarifa por noche</h6>
                                        <span class="font-bold">
                                            {{ format_mxn(toCents(firstNightRate(room))) }}
                                        </span>
                                    </div>
        
                                    <div class="flex flex-col text-right">
                                        <span>
                                            {{ gapDate() }}, {{ data.adults }}
                                            {{ Number(data.adults) === 1 ? 'adulto' : 'adultos' }}
                                        </span>
                                    </div>
                                </div>
        
                                <el-divider />
        
                                <div class="text-sm text-gray-700">
                                    <div class="flex items-center gap-1 text-xs italic underline">
                                        <span class="font-bold">
                                            Total: {{ format_mxn(totalCents(room), true) }}
                                        </span>
        
                                        <!-- Desglose por noche -->
                                        <el-dropdown placement="top-start">
                                            <InfoSvg :width="15" :height="15" />
                                            <template #dropdown>
                                                <el-dropdown-menu>
                                                    <div class="flex flex-col px-3 py-1 min-w-[220px]">
                                                        <p class="font-semibold text-center">Desglose de costos</p>
                                                        <div class="h-[1px] w-full bg-gray-200 my-2"></div>
        
                                                        <div v-for="r in room.rates" :key="r.date"
                                                            class="flex justify-between gap-2">
                                                            <span class="whitespace-nowrap">
                                                                {{ new Date(r.date).toLocaleDateString('es-MX') }}
                                                            </span>
                                                            <span class="font-medium">
                                                                {{ format_mxn(toCents(r.rate)) }}
                                                            </span>
                                                        </div>
        
                                                        <div class="h-[1px] w-full bg-gray-200 my-2"></div>
                                                        <p class="flex justify-between">
                                                            <span>Habitaciones</span>
                                                            <span>x {{ data.numHabs }}</span>
                                                        </p>
                                                        <p class="text-right font-semibold">
                                                            {{ format_mxn(totalCents(room), true) }}
                                                        </p>
                                                        <p class="text-xs">* IVA inlcuído</p>
                                                    </div>
                                                </el-dropdown-menu>
                                            </template>
                                        </el-dropdown>
                                    </div>
                                </div>
        
                                <div class="flex flex-col md:flex-row md:gap-3">
                                    <el-button class="w-full mt-2 nuve-btn" :loading="isLoading"
                                        @click="pendingRoom = room; goToCheckout(room); isPayingAtHotel = false;">
                                        Pagar en línea
                                    </el-button>
                                    <el-button class="w-full mt-2" style="margin-left:0px !important;" type="default" :loading="isLoading"
                                        @click="pendingRoom = room; isPayingAtHotel = true; bookingInReception(room);">
                                        Pagar en recepción
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
                <div>
                    <span class="text-2xl text-gray-900 text-center font-bold">Nuve Gomez.</span>
                    <article v-for="room in roomsGomez" :key="room.code"
                        class="w-full md:w-[500px] flex flex-col gap-4 outline outline-1 outline-gray-300 rounded-lg p-3 mt-3">
                        <div class="w-full h-[200px] rounded-md overflow-hidden">
                            <!-- Imagen fija del diseño original -->
                            <el-carousel trigger="click" height="200px" :interval=6000>
                                <template v-for="image in imagesRooms['corregidora'][room.code]" :key="image">
                                    <el-carousel-item>
                                        <img :src="image" alt="" class="w-full h-full object-cover">
                                    </el-carousel-item>
                                </template>
                            </el-carousel>
                        </div>
        
                        <div class="w-full flex flex-col gap-3">
                            <header class="flex flex-col">
                                <h3 class="font-extrabold text-2xl text-gray-800">
                                    {{ room.name || typeHabs[room.code] }}
                                </h3>
                                <span class="w-fit text-gray-400 text-xs font-semibold">
                                    <!-- {{ room.plan || 'Plan no especificado' }} -->
                                      Desayuno incluído por promoción
                                </span>
                            </header>
        
                            <div class="flex-1">
                                <div class="w-full flex justify-between gap-3 text-sm text-gray-700">
                                    <div class="flex flex-col">
                                        <h6 class="font-semibold text-base">Tarifa por noche</h6>
                                        <span class="font-bold">
                                            {{ format_mxn(toCents(firstNightRate(room))) }}
                                        </span>
                                    </div>
        
                                    <div class="flex flex-col text-right">
                                        <span>
                                            {{ gapDate() }}, {{ data.adults }}
                                            {{ Number(data.adults) === 1 ? 'adulto' : 'adultos' }}
                                        </span>
                                    </div>
                                </div>
        
                                <el-divider />
        
                                <div class="text-sm text-gray-700">
                                    <div class="flex items-center gap-1 text-xs italic underline">
                                        <span class="font-bold">
                                            Total: {{ format_mxn(totalCents(room), true) }}
                                        </span>
        
                                        <!-- Desglose por noche -->
                                        <el-dropdown placement="top-start">
                                            <InfoSvg :width="15" :height="15" />
                                            <template #dropdown>
                                                <el-dropdown-menu>
                                                    <div class="flex flex-col px-3 py-1 min-w-[220px]">
                                                        <p class="font-semibold text-center">Desglose de costos</p>
                                                        <div class="h-[1px] w-full bg-gray-200 my-2"></div>
        
                                                        <div v-for="r in room.rates" :key="r.date"
                                                            class="flex justify-between gap-2">
                                                            <span class="whitespace-nowrap">
                                                                {{ new Date(r.date).toLocaleDateString('es-MX') }}
                                                            </span>
                                                            <span class="font-medium">
                                                                {{ format_mxn(toCents(r.rate)) }}
                                                            </span>
                                                        </div>
        
                                                        <div class="h-[1px] w-full bg-gray-200 my-2"></div>
                                                        <p class="flex justify-between">
                                                            <span>Habitaciones</span>
                                                            <span>x {{ data.numHabs }}</span>
                                                        </p>
                                                        <p class="text-right font-semibold">
                                                            {{ format_mxn(totalCents(room), true) }}
                                                        </p>
                                                        <p class="text-xs">* IVA inlcuído</p>
                                                    </div>
                                                </el-dropdown-menu>
                                            </template>
                                        </el-dropdown>
                                    </div>
                                </div>
        
                                <div class="flex flex-col md:flex-row md:gap-3">
                                    <el-button class="w-full mt-2 nuve-btn" :loading="isLoading"
                                        @click="pendingRoom = room; goToCheckout(room); isPayingAtHotel = false;">
                                        Pagar en línea
                                    </el-button>
                                    <el-button class="w-full mt-2" style="margin-left:0px !important;" type="default" :loading="isLoading"
                                        @click="pendingRoom = room; isPayingAtHotel = true; bookingInReception(room);">
                                        Pagar en recepción
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
            <span v-else class="text-sm text-gray-500 text-center">Por favor, seleccione fechas válidas para ver las habitaciones disponibles.</span>
        </div>
    </section>
    <UserComplementaryData v-model:visible="showUserComplementaryData" @send-data="onReceiveData" :is-paying-at-hotel="isPayingAtHotel" />
    <Footer class="mt-14" />
</template>

<script>
import BedSvg from '@/Components/BedSvg.vue';
import InfoSvg from '@/Components/InfoSvg.vue';
import WifiSvg from '@/Components/WifiSvg.vue';
import ACSvg from '@/Components/ACSvg.vue';
import Header from '@/Components/Header.vue';
import axios from 'axios';
import { ElNotification, FIRST_LAST_KEYS, uploadBaseProps, ElCarousel, ElCarouselItem, ElMessageBox, ElMessage } from 'element-plus';
import TVSvg from '@/Components/TVSvg.vue';
import ParkingSvg from '@/Components/ParkingSvg.vue';
import UserComplementaryData from '@/Components/UserComplementaryData.vue';
import { markRaw } from 'vue';
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
                "DO": "Doble",
                "Q": "Queen",
                "S": "Standard",
                "SK": "Standard King Size",
                "S-": "Sencilla",
                "D-": "Doble",
            },

            imagesRooms: {
                "corregidora" : {
                    "1K": [
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-1.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-2.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-3.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-4.webp",
                    ],
                    "S-": [
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-1.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-2.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-3.webp",
                        "/img/hotel_corregidora/habitacion-sencilla-corregidora-4.webp",
                    ],
                    "DO": [
                        "/img/hotel_corregidora/habitacion-doble-corregidora-1.webp",
                        "/img/hotel_corregidora/habitacion-doble-corregidora-2.webp",
                    ],
                    "D-": [
                        "/img/hotel_corregidora/habitacion-doble-corregidora-1.webp",
                        "/img/hotel_corregidora/habitacion-doble-corregidora-2.webp",
                    ]
                },
            },

            pendingRoom: null,
            pendingHotelCode: null,
            isPayingAtHotel: false,
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
        async bookingInReception(room) {
            room = room || this.pendingRoom
            const hotelCode = room?.hotel_code || this.pendingHotelCode
            this.pendingRoom = room
            this.pendingHotelCode = hotelCode
            
            if (!this.isLogged && !this.userInfo) {
                this.showUserComplementaryData = true
                return
            }

            if(this.isLogged) {
                this.userInfo = {
                    name: this.infoUser.name,
                    lastname: this.infoUser.lastname,
                    email: this.infoUser.email,
                    phone: this.infoUser.phone,
                }

                ElMessageBox.confirm(
                    `<strong>Estos son los datos de la habitación:</strong><br><br>
                    <strong>Hotel:</strong> ${room.hotel_name || this.hotelName(hotelCode)}<br>
                    <strong>Habitación:</strong> ${room.name || this.typeHabs[room.code]}<br>
                    <strong>Plan:</strong> ${room.plan || 'Plan no especificado'}<br>
                    <strong>Check-in:</strong> ${this.data.dateIni}<br>
                    <strong>Check-out:</strong> ${this.data.dateFin}<br>
                    <strong>Adultos:</strong> ${this.data.adults}<br>
                    <strong>Habitaciones:</strong> ${this.data.numHabs}<br><br>
                    <small>Los datos de contacto son los datos de tu perfil</small>`,
                    'Confirmar Reservación',
                    {
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancelar',
                        type: 'warning',
                        dangerouslyUseHTMLString: true, // <--- ESTA ES LA CLAVE
                    }
                )
                    .then(() => {                        
                        this.saveBookingInReception();
                    })
                    .catch(() => {
                        ElMessage({
                            type: 'info',
                            message: 'Operación cancelada',
                        })
                    })
                return;
            }
            this.saveBookingInReception();

        },
        onReceiveData(data) {
            this.userInfo = data;
            // if (!this.userInfo || typeof this.userInfo !== 'object') return;

            // const hasAllFields = this.userInfoKeys.every((key) => {
            //     const value = this.userInfo[key];
            //     return value !== null && value !== undefined && String(value).trim() !== '';
            // });

            // if (!hasAllFields) {
            //     console.warn('Faltan campos en userInfo, no se puede continuar');
            //     return;
            // }

            // Si está todo bien, ahora sí vamos al checkout

            !this.isPayingAtHotel ? this.goToCheckout() : this.bookingInReception();
        },
        onFinish() {
            window.location.reload();
        },
        saveBookingInReception() {
            const amountCents = this.totalCents(this.pendingRoom);
            const payload = {
                hotel_code: this.pendingHotelCode || this.pendingRoom.hotel_code,
                hotel_name: this.pendingRoom.hotel_name || this.hotelName(this.pendingHotelCode || this.pendingRoom.hotel_code),
                room_code: this.pendingRoom.code,
                room_name: this.pendingRoom.name,
                plan: this.pendingRoom.plan,
                check_in: this.data.dateIni,
                check_out: this.data.dateFin,
                adults: this.data.adults,
                num_habs: this.data.numHabs,
                user_info: {
                    name: this.userInfo.name,
                    lastname: this.userInfo.lastname,
                    email: this.userInfo.email,
                    phone: this.userInfo.phone,
                },
                amount_cents: amountCents,
                amount: amountCents / 100,
            };

            axios.post('/create-booking-reception', payload)
            .then((response) => {
                if(response.status === 201) {
                    ElNotification({
                        title: 'Éxito',
                        message: 'Reservación creada con éxito para pago en recepción. Serás redirigido a la página de inicio en unos segudos.',
                        type: 'success'
                    })
                    this.showUserComplementaryData = false;
                    setTimeout(() => {
                        router.get('/checkout/success/reception', payload)
                    }, 3000);
                }
            })
            .catch(() => {
                ElNotification({
                    title: 'Error',
                    message: 'Ocurrió un error al crear la reservación para pago en recepción.',
                    type: 'error'
                })
            })
        },
        hotelName(hotelCode) {
            const names = {
                torreon: 'Nuve Torreón',
                gomez: 'Nuve Gomez',
            };

            return names[hotelCode] || hotelCode || 'Hotel';
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
