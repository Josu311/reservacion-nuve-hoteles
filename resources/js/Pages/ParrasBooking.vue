<template>
    <Head>
        <title>Reserva en Nuve Parras</title>
        <meta name="description" content="Consulta disponibilidad y reserva en Nuve Parras desde un buscador dedicado para este hotel." />
        <link rel="icon" href="/favicon-parras.ico" />
    </Head>

    <HeaderParras />

    <section class="relative min-h-[680px] bg-[url(/img/hotels-38.webp)] bg-cover bg-center text-white flex items-center">
        <div class="absolute inset-0 bg-slate-950/55"></div>
        <div class="relative z-10 max-w-6xl mx-auto w-full px-4 py-20">
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] items-center">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs uppercase tracking-[0.25em]">
                        Nuve Parras
                    </span>
                    <h1 class="mt-6 text-4xl md:text-6xl font-bold leading-tight">Tu buscador exclusivo para reservar en Parras</h1>
                    <p class="mt-5 text-base md:text-lg text-white/80 max-w-xl">
                        Este flujo consulta únicamente la disponibilidad de Nuve Parras y te lleva a una página separada para completar la reserva.
                    </p>
                </div>

                <div class="rounded-[28px] border border-white/15 bg-white/12 backdrop-blur-md p-6 md:p-8 shadow-2xl">
                    <p class="text-sm uppercase tracking-[0.3em] text-white/60">Disponibilidad</p>
                    <h2 class="mt-2 text-2xl font-semibold">Buscar estancia</h2>

                    <el-form class="mt-6" :model="form">
                        <div class="grid grid-cols-1 gap-3">
                            <el-form-item class="mi-input-custom !mb-0">
                                <el-date-picker
                                    v-model="fullDate"
                                    type="daterange"
                                    range-separator="a"
                                    start-placeholder="Check-in"
                                    end-placeholder="Check-out"
                                    popper-class="dp-mobile"
                                    :popper-options="popperOptions"
                                    :teleported="!isMobile"
                                    :disabled-date="disabledBeforeToday"
                                    style="width: 100%;"
                                />
                            </el-form-item>
                            <el-form-item class="mi-input-custom !mb-0">
                                <el-input-number v-model="form.numHabs" :min="1" placeholder="Habitaciones" style="width: 100%;" />
                            </el-form-item>
                            <el-form-item class="mi-input-custom !mb-0">
                                <el-input-number v-model="form.adults" :min="1" placeholder="Adultos" style="width: 100%;" />
                            </el-form-item>
                            <el-button class="nuve-btn w-full" :loading="isLoading" @click="searchHabs()" size="large">
                                Buscar disponibilidad
                            </el-button>
                        </div>
                    </el-form>
                </div>
            </div>
        </div>
    </section>

    <section class="flex justify-center items-center bg-nuve-express-beige pt-44 md:pt-32 pb-5">
        <div class="max-w-[1440px] flex flex-col-reverse lg:flex-row items-center gap-5 mx-auto">
            <img class="w-full lg:w-[50%] h-[500px] object-cover" src="/img/hotels-22.webp" alt="">
            <div class="w-full lg:w-[50%] flex flex-col items-start justify-center gap-5 text-sm p-3">
                <div class="w-full flex flex-col gap-1">
                    <span class="text-nuve-express-blue text-lg font-semibold text-center lg:text-start">Nuve Parras</span>
                    <h2 class="text-2xl md:text-5xl font-semibold uppercase text-center lg:text-start">Bienvenido a Nuve Parras
                    </h2>
                </div>

                <p class="text-center lg:text-start">Contamos con habitaciones donde encontrarás los mejores espacios y nuestras excelentes servicios disponible spara ti.</p>
                <p class="text-center lg:text-start">Disfruta de la mejor ubicación en habitacióon preferencial con una cama, amenidades y servicios orientados a tu descanso</p>
                <a href="/nosotros" class="w-fit mx-auto lg:mx-0 px-6 py-2 bg-nuve-parras-beige text-gray-800 text-base font-semibold">Conoce más</a>
            </div>
        </div>
    </section>

    <ExperienciasNuve />

    <section class="relative h-[600px] flex items-center justify-center bg-[url('/img/parras-paisaje.webp')] bg-no-repeat bg-cover bg-bottom">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="w-full max-w-[1440px] text-center z-[1] text-white">
            <p class="text-lg font-semibold pb-4">Nuve Parras</p>
            <h1 class="text-2xl md:text-5xl font-bold uppercase">Más que una estancia</h1>
            <p class="pt-6 px-5 md:px-10">En Nuve Parras creemos que cada estancia debe sentirse especial. Nuestro hotel combina diseño contemporáneo, confort y la esencia de uno de los destinos más emblemáticos del norte de México. Ubicados cerca del corazón de Parras, ofrecemos un espacio pensado para descansar, explorar y disfrutar cada momento de tu visita.</p>
            <a href="/nosotros" class="w-fit inline-block px-6 py-4 font-semibold bg-nuve-parras-beige text-gray-800 mt-4">Sobre nosotros</a>
        </div>
    </section>

    <Footer logo-src="/img/parras/hotel-parras-logo.webp" />
</template>

<script>
import Footer from '@/Components/Footer.vue';
import ExperienciasNuve from '@/Components/Parras/ExperienciasNuve.vue';
import HeaderParras from '@/Components/Parras/HeaderParras.vue';
import { Head, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';

export default {
    components: {
        Footer,
        Head,
        HeaderParras,
        ExperienciasNuve,
    },
    data() {
        return {
            fullDate: null,
            form: {
                dateIni: '',
                dateFin: '',
                numHabs: null,
                adults: null,
            },
            isLoading: false,
            isMobile: window.innerWidth <= 640,
            popperOptions: {
                strategy: 'fixed',
                modifiers: [
                    { name: 'preventOverflow', options: { boundary: 'viewport', padding: 8 } },
                    { name: 'flip', options: { fallbackPlacements: ['bottom-start', 'top-start'] } },
                    { name: 'computeStyles', options: { adaptive: false } },
                ],
            },
        };
    },
    mounted() {
        this.onResize = () => {
            this.isMobile = window.innerWidth <= 640;
        };

        window.addEventListener('resize', this.onResize);
        window.addEventListener('orientationchange', this.onResize);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.onResize);
        window.removeEventListener('orientationchange', this.onResize);
    },
    methods: {
        disabledBeforeToday(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date < today;
        },
        searchHabs() {
            this.isLoading = true;

            if (Array.isArray(this.fullDate) && this.fullDate.length === 2) {
                const [start, end] = this.fullDate;
                this.form.dateIni = start;
                this.form.dateFin = end;
            }

            if (this.isSameDaySearch(this.form.dateIni, this.form.dateFin)) {
                this.isLoading = false;
                ElNotification({
                    title: 'Fecha inválida',
                    message: 'No se pueden buscar habitaciones para entrada y salida dentro del mismo día.',
                    type: 'warning',
                });
                return;
            }

            router.post('/parras/disponibilidad', this.form, {
                onError: () => {
                    ElNotification({
                        title: 'Error',
                        message: 'Debes llenar todos los campos del formulario',
                        type: 'error',
                    });
                },
                onFinish: () => {
                    this.isLoading = false;
                },
            });
        },
        isSameDaySearch(dateIni, dateFin) {
            if (!dateIni || !dateFin) return false;

            const start = new Date(dateIni);
            const end = new Date(dateFin);

            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return false;

            start.setHours(0, 0, 0, 0);
            end.setHours(0, 0, 0, 0);

            return start.getTime() === end.getTime();
        },
    },
};
</script>

<style scoped>
.nuve-btn {
    background-color: #F3EFE6;
    border-color: #F3EFE6;
    color: #1f2937;
    font-weight: 600;
}

.nuve-btn:hover,
.nuve-btn:focus {
    background-color: #e4ded2;
    border-color: #e4ded2;
    color: #1f2937;
}

.mi-input-custom :deep(.el-input__wrapper) {
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.95);
}
</style>
