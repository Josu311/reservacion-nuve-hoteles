<template>
    <Head>
        <title>Reserva en Nuve Parras</title>
        <meta name="description" content="Consulta disponibilidad y reserva en Nuve Parras desde un buscador dedicado para este hotel." />
    </Head>

    <Header />

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

    <Footer />
</template>

<script>
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import { Head, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';

export default {
    components: {
        Footer,
        Head,
        Header,
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

.mi-input-custom :deep(.el-input__wrapper) {
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.95);
}
</style>
