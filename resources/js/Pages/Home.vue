<template>
    <section class="h-screen">

        <Head>
            <title>Nuve Express | Hoteles en Torreón, Coahuila</title>
            <meta name="description" content="Hospédate en Nuve Express en Torreón, Coahuila: cómodo y accesible. Reserva en línea en minutos y elige la mejor opción para viajes de trabajo o estancias largas.">
            
            <meta property="og:type" content="website" />
            <meta property="og:site_name" content="Nuve Express">
            <meta property="og:title" content="Reserva en Nuve Express | Hoteles en Torreón, Coahuila"/>
            <meta property="og:description" content="Hospedaje cómodo y accesible en Torreón. Reserva en línea en minutos y elige la mejor opción para viajes de trabajo o estancias largas."/>
            <meta property="og:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png"/>
            <meta property="og:url" content="https://nuveexpress.com.mx/"/>

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Reserva en Nuve Express | Hoteles en Torreón, Coahuila">
            <meta name="twitter:description" content="Hoteles con ubicación estratégica en Torreón. Reserva tu estancia en línea en minutos.">
            <meta property="twitter:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png"/>

            <link rel="canonical" href="https://nuveexpress.com.mx/" />
        </Head>
        <Header />
        <section class="flex flex-col h-[500px]" id="reservar">
            <div class=" relative h-[500px] md:h-screen w-full">
                <img class="w-full h-[500px] object-cover object-top" src="/img/fachada-1.webp" alt="">
                <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
                <div
                    class="h-[500px] flex flex-col items-center justify-end gap-5 absolute inset-0 z-10 my-auto text-white px-7">
                    <h2 class="text-4xl md:text-6xl text-center font-semibold leading-[1] uppercase">Hospédate en
                        Torreón</h2>
                    <p class="text-xl md:text-3xl font-semibold text-center">con el mejor precio y comodidad</p>
                    <div class="w-full max-w-[1440px] bg-white p-5 ml-7 mr-7 rounded-2xl -mb-28 shadow-md">
                        <h4 class="text-3xl text-center text-nuve-express-orange font-semibold">Reservar ahora</h4>
                        <el-form class="mt-4" :model="form" v-if="isInProduction">
                            <div class="w-full grid grid-cols-1 grid-rows-4 md:grid-cols-4 md:grid-rows-1 gap-2">
                                <el-form-item class="w-full mi-input-custom" style="margin-bottom: 0px;">
                                    <el-date-picker
                                    v-model="fullDate" 
                                    type="daterange" 
                                    range-separator="To"
                                    start-placeholder="Checkin"
                                    end-placeholder="Checkout"    
                                    popper-class="dp-mobile"
                                    :popper-options="popperOptions"
                                    :teleported="!isMobile"
                                    :disabled-date="disabledBeforeToday" style="width: 100%;" />
                                </el-form-item>
                                <!-- <el-form-item class="w-full mi-input-custom" style="margin-bottom: 0px;">
                                    <el-date-picker v-model="form.dateFin" type="date" placeholder="Fecha de salida"
                                        :disabled-date="disabledBeforeStart" style="width: 100%;" />
                                </el-form-item> -->
                                <el-form-item class="w-full mi-input-custom" style="margin-bottom: 0px;">
                                    <el-input-number v-model="form.numHabs" :min="1" placeholder="Habitaciones"
                                        style="width: 100%;" />
                                </el-form-item>
                                <el-form-item class="w-full mi-input-custom" style="margin-bottom: 0px;">
                                    <el-input-number v-model="form.adults" :min="1" placeholder="Adultos"
                                        style="width: 100%;" />
                                </el-form-item>
                                <el-button class="col-span-1 nuve-btn" :disabled="enabledButton"
                                    :loading="isLoading" type="warning" @click="searchHabs()"
                                    size="large">Buscar</el-button>
                            </div>
                        </el-form>
                    </div>
                </div>
            </div>
        </section>
        <section class="w-full p-3 md:p-8 mt-32 md:mt-28">
            <div class="max-w-[1600px] flex flex-col-reverse lg:flex-row items-center gap-5 mx-auto">
                <img class="w-full lg:w-[50%] h-[500px] object-cover" src="/img/fachada-2.webp" alt="">
                <div class="w-full lg:w-[50%] flex flex-col items-start justify-center gap-5 text-[22px]">
                    <div class="w-full flex flex-col gap-1">
                        <span class="text-nuve-express-orange text-lg font-semibold text-center md:text-start">Nuve
                            Express</span>
                        <h2 class="text-2xl md:text-4xl font-semibold uppercase text-center md:text-start">Más que un
                            hotel, tu
                            <br>aliado en
                            Torreón
                        </h2>
                    </div>

                    <p class="text-center md:text-start">Nuve Express es una cadena de hoteles ubicados en el centro de
                        la ciudad de
                        Torreón,
                        Coahuila.
                    </p>
                    <p class="text-center md:text-start">Nuestra misión es brindar el mejor trato y servicio a todos
                        nuestros
                        huéspedes.</p>
                    <p class="text-center md:text-start">Ven y compruébalo, somos tu mejor opción en la ciudad.</p>

                </div>
            </div>
        </section>
        <section class="w-full flex flex-col gap-5 bg-nuve-express-orange p-6 md:p-12">
            <div class="flex flex-col items-center">
                <span class="text-sm font-semibold uppercase">Nuestros servicios</span>
                <h2 class="text-2xl text-white font-semibold uppercase text-center">Comodidad en cada detalle</h2>
            </div>
            <div class="grid grid-cols-2 grid-rows-4 md:grid-cols-4 md:grid-rows-2 gap-5">
                <div class="text-white">
                    <FolderOpenSvg :width="32" :height="32" />
                    <p class="font-semibold">Convenios mensuales</p>
                    <p class="text-sm">para empresas</p>
                </div>
                <div class="text-white">
                    <SparklesSvg :width="32" :height="32" />
                    <p class="font-semibold">Habitaciones equipadas</p>
                    <p class="text-sm">con refrigerador, microondas y escritorio</p>
                </div>
                <div class="text-white">
                    <WifiSvg :width="32" :height="32" />
                    <p class="font-semibold">Wi-Fi</p>
                    <p class="text-sm">de alta velocidad incluído</p>
                </div>
                <div class="text-white">
                    <HouseUserSvg :width="32" :height="32" />
                    <p class="text-sm">Perfecto para</p>
                    <p class="font-semibold">Largas estancias</p>
                </div>
                <div class="text-white">
                    <CreditCardSvg :width="32" :height="32" />
                    <p class="font-semibold">Facturación</p>
                    <p class="text-sm">fácil y rápido</p>
                </div>
                <div class="text-white">
                    <SuitcaseSvg :width="32" :height="32" />
                    <p class="font-semibold">Guarda equipaje</p>
                    <p class="text-sm hidden"></p>
                </div>
                <div class="text-white">
                    <HappyFaceSvg :width="32" :height="32" />
                    <p class="font-semibold">Habitaciones cómodas</p>
                    <p class="text-sm">diseñadas para tu descanso</p>
                </div>
                <div class="text-white">
                    <ClockSvg :width="32" :height="32" />
                    <p class="font-semibold">Early check-in / Late check-out</p>
                    <p class="text-sm">(según disponibilidad)</p>
                </div>
            </div>
        </section>
        <section class="mt-16">
            <div class="flex flex-col items-center">
                <span class="text-nuve-express-orange uppercase font-semibold">Nuve Express</span>
                <h2 class="text-2xl uppercase font-semibold text-center">Donde tu empresa nos necesita</h2>
            </div>
            <div
                class="max-w-[500px] grid grid-cols-1 grid-rows-1 gap-16 md:gap-0 place-content-center mx-auto mt-10">
                <article class="w-[90%] shadow-lg mx-auto">
                    <img class="w-full h-[300px] object-cover" src="/img/nuve-express-corregidora.png" height="300"
                        alt="">
                    <div class="flex flex-col items-center px-11 pt-5">
                        <p class="text-center text-sm font-semibold">Nuve Express <br>Corregidora</p>
                        <div class="flex items-center gap-2 mt-4 mb-6">
                            <PinAddressFilledSvg :width="20" :height="20" :fill="'#000000'" />
                            <p class="text-xs text-center">Calle Corregidora #130, entre González Ortega y Degollados
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="flex flex-col mt-16">
            <div class=" relative h-[500px] w-full">
                <img class="w-full h-[500px] object-cover object-top" src="/img/fachada-1.webp" alt="">
                <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
                <div
                    class="max-w-[1600px] h-[500px] flex flex-col items-center justify-center gap-5 absolute inset-0 z-10 m-auto text-white p-3 md:p-0">
                    <div class="text-center">
                        <p class="text-lg text-nuve-express-orange font-semibold">Nuve Express</p>
                        <h2 class="text-3xl md:text-4xl text-center font-semibold leading-[1] uppercase">
                            Más que un hotel, tu <br>aliado en Torreón
                        </h2>
                    </div>
                    <p class="text-center text-[22px]">
                        En Nuve Express entendemos lo importante que es contar con un espacio cómodo, práctico y
                        confiable durante tus proyectos en la ciudad.
                    </p>
                    <p class="text-center text-[22px]">
                        Nuestras tarifas corporativas, convenios mensuales
                        y habitaciones totalmente equipadas hacen que tu estancia sea tan productiva como cómoda.
                    </p>
                </div>
            </div>
        </section>

        <section class="mt-16 pb-16">
            <div class="flex flex-col items-center">
                <span class="text-nuve-express-orange uppercase font-semibold">Nuve Express</span>
                <h2 class="text-2xl md:text-3xl uppercase font-semibold text-center">Opciones que se adaptan a tu
                    empresa</h2>
                <BookNowButton text="Solicita tu convenio" path="https://wa.me/5214492176423"
                    class="fit-content mt-4" />
            </div>

            <div class="max-w-[1600px] flex flex-col lg:flex-row gap-14 md:gap-5 mx-auto mt-10 px-4">
                <div class="w-full flex flex-col items-center gap-5 text-center box-customize px-11 py-5">
                    <BedSvg2 :width="50" :height="50" />
                    <p class="text-center text-xl font-semibold">Habitación sencilla</p>
                    <p>1 cama matrimonial para <br>máximo 2 personas</p>
                </div>

                <div class="w-full flex flex-col items-center gap-5 text-center box-customize px-11 py-5">
                    <div class="flex items-center gap-2">
                        <BedSvg2 :width="50" :height="50" />
                        <BedSvg2 :width="50" :height="50" />
                    </div>
                    <p class="text-center text-xl font-semibold">Habitación doble</p>
                    <p>2 cama matrimoniales para <br>máximo 4 personas</p>
                </div>
            </div>
        </section>

        <Footer />
    </section>
</template>

<script>
import ArrowRightSvg from '@/Components/ArrowRightSvg.vue';
import BookNowButton from '@/Components/BookNowButton.vue';
import ClockSvg from '@/Components/ClockSvg.vue';
import CreditCardSvg from '@/Components/CreditCardSvg.vue';
import FolderOpenSvg from '@/Components/FolderOpenSvg.vue';
import HappyFaceSvg from '@/Components/HappyFaceSvg.vue';
import Header from '@/Components/Header.vue';
import HouseUserSvg from '@/Components/HouseUserSvg.vue';
import PinAddressFilledSvg from '@/Components/PinAddressSvgFilled.vue';
import PinAddressSvg from '@/Components/PinAddressSvg.vue';
import SparklesSvg from '@/Components/SparklesSvg.vue';
import SuitcaseSvg from '@/Components/SuitcaseSvg.vue';
import WifiSvg from '@/Components/WifiSvg.vue';
import { Head, router } from '@inertiajs/vue3'
import { ElNotification } from 'element-plus';
import BedSvg2 from '@/Components/BedSvg2.vue';
import Footer from '@/Components/Footer.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';

export default {
    components: {
        Head,
        Header,
        ArrowRightSvg,
        BookNowButton,
        FolderOpenSvg,
        SparklesSvg,
        WifiSvg,
        HouseUserSvg,
        CreditCardSvg,
        SuitcaseSvg,
        HappyFaceSvg,
        ClockSvg,
        PinAddressSvg,
        PinAddressFilledSvg,
        BedSvg2,
        WhatsappButton,
        Footer,
    },
    data() {
        return {
            isLoading: false,
            isInProduction: true,
            enabledButton: false,
            fullDate: null,
            form: {
                dateIni: "",
                dateFin: "",
                // typeHab: "",
                numHabs: null,
                adults: null

            },

            typeHabs: [
                {
                    value: "1K",
                    label: "King size"
                },
                {
                    value: "DO",
                    label: "Doble"
                },
                {
                    value: "MK",
                    label: "Queen"
                },
                {
                    value: "SD",
                    label: "Standard"
                },
                {
                    value: "SK",
                    label: "Standard King Size"
                },
            ],
            isMobile: window.innerWidth <= 640,
            popperOptions: {
        strategy: 'fixed',
            modifiers: [
            { name: 'preventOverflow', options: { boundary: 'viewport', padding: 8 } },
            { name: 'flip', options: { fallbackPlacements: ['bottom-start', 'top-start'] } },
            // CLAVE: evita cálculos "raros" en algunos móviles
            { name: 'computeStyles', options: { adaptive: false } },
            ],
        },
        }
    },

    mounted() {
        // Mantener isMobile actualizado (resize/orientation)
        this._onResize = () => {
        this.isMobile = window.innerWidth <= 640
        }
        window.addEventListener('resize', this._onResize)
        window.addEventListener('orientationchange', this._onResize)
    },

    beforeUnmount() {
        window.removeEventListener('resize', this._onResize)
        window.removeEventListener('orientationchange', this._onResize)
    },




    methods: {
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
    }

}
</script>

<!-- Tipografía global -->
<style>
p,
a,
h2 {
    line-height: 22px;
}

html {
    scroll-behavior: smooth;
}
</style>

<!-- Estilos específicos del componente -->
<style scoped>
.nuve-btn {
    background-color: #de813e;
    border-color: #de813e;
}

/* Opcional: hover / focus */
.nuve-btn:hover,
.nuve-btn:focus {
    background-color: #c86f32;
    border-color: #c86f32;
}

/* Opcional: cuando está disabled, para que no se vea gris */
.nuve-btn.is-disabled {
    background-color: #de813e80;
    /* mismo color pero más clarito */
    border-color: #de813e80;
}

.box-customize {
    box-shadow: 0px 0px 21px -6px #000000;
}

.mi-input-custom :deep(.el-input__wrapper) {
    border-color: #424242;
    box-shadow: 0 0 0 1px #424242;
}
</style>

<style>
/* =========================================
   Element Plus DatePicker (daterange) - Mobile responsive (2 meses en columna)
   Requiere: popper-class="dp-mobile"
========================================= */

@media (max-width: 640px) {

  /* POPPER ROOT (tu popper-class se inyecta aquí) */
  .dp-mobile {
    position: fixed !important;
    left: 50% !important;
    right: auto !important;
    width: calc(100vw - 16px) !important;
    max-width: calc(100vw - 16px) !important;
    box-sizing: border-box !important;
    transform: translateX(-50%) !important;
    margin: 0 !important;
    z-index: 9999 !important;
  }

  /* Asegura que el panel NO tenga anchos fijos internos */
  .dp-mobile .el-picker-panel,
  .dp-mobile .el-picker-panel__body,
  .dp-mobile .el-date-range-picker,
  .dp-mobile .el-date-range-picker__content {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
  }

  /* ======= CAMBIO CLAVE: 2 meses en columna (NO ocultar el derecho) ======= */
  .dp-mobile .el-picker-panel__body{
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
  }

  .dp-mobile .el-date-range-picker__content.is-left,
  .dp-mobile .el-date-range-picker__content.is-right{
    display: block !important;          /* asegura que el derecho se vea */
    width: 100% !important;
    padding: 8px !important;
    border-right: 0 !important;
  }

  /* Tabla del calendario siempre al 100% */
  .dp-mobile .el-date-table {
    width: 100% !important;
    table-layout: fixed !important;
  }

  /* Celdas más compactas */
  .dp-mobile .el-date-table td {
    padding: 0 !important;
  }

  .dp-mobile .el-date-table-cell {
    height: 34px !important;
    padding: 0 !important;
  }

  .dp-mobile .el-date-table-cell__text {
    width: 28px !important;
    height: 28px !important;
    line-height: 28px !important;
    font-size: 12px !important;
  }

  /* (Opcional) Inputs del daterange al 100% */
  .el-date-editor--daterange {
    width: 100% !important;
  }
}

</style>