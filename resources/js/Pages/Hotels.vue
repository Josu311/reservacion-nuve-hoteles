<template>
    <Head>
        <title>Ubicaciones Nuve Express en Torreón | Abasolo y Corregidora</title>
        <meta name="description" content="Conoce las ubicaciones de Nuve Express en Torreón, Coahuila: Abasolo (Centro) y Corregidora. Consulta el mapa y reserva tu habitación en línea.">
        
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Nuve Express">
        <meta property="og:title" content="Nosotros | Nuve Express – Hoteles en Torreón"/>
        <meta property="og:description" content="Encuentra nuestras ubicaciones en Torreón, Coahuila. Revisa el mapa de Abasolo y Corregidora y reserva tu estancia en línea."/>
        <meta property="og:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png"/>
        <meta property="og:url" content="https://nuveexpress.com.mx/hotels"/>

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Ubicaciones Nuve Express en Torreón | Abasolo y Corregidora">
        <meta name="twitter:description" content="Conoce nuestras ubicaciones en Torreón, Coahuila. Mapa y reserva en línea en Nuve Express.">
        <meta name="twitter:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png">

        <link rel="canonical" href="https://nuveexpress.com.mx/hoteles" />
    </Head>
    <Header />

    <section class="relative h-[700px] bg-[url(/img/home-1.webp)] bg-cover bg-bottom bg-fixed flex flex-col items-center justify-center gap-5 text-white">
        <div class="absolute inset-0 bg-[#172a3a]/50"></div>
        <div class="max-w-xl mx-auto flex flex-col items-center justify-center text-center pt-32 z-[1]">
            <h2 class="uppercase font-bold text-5xl">Nuestros hoteles</h2>
        </div>
    </section>

    <section class="max-w-6xl mx-auto mt-20 px-2">
        <div class="flex flex-col gap-10">
            <div class="flex flex-col-reverse md:flex-row gap-10">
                <img class="w-full md:w-1/2 h-auto" src="/img/hotels-1.webp" alt="">
                <div class="w-full md:w-1/2 flex flex-col justify-around">
                    <span class="text-xs text-nuve-hoteles-blue text-center md:text-start font-semibold">Desde $1,750/noche</span>
                    <div class="flex flex-col items-center md:items-start gap-3 mt-5">
                        <h2 class="uppercase font-bold text-2xl md:text-5xl">Torreón, Coah.</h2>
                        <ul class="flex flex-col gap-3 [&>li]:text-sm [&>li]:text-gray-800">
                            <li class="flex items-center gap-3">
                                <PinSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Av Abasolo 901, Primero de Cobián Centro, 27000 Torreón, Coah.</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-in 15.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-out 12.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <WifiSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Wi-Fi</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <BoardRoom :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Salas de Juntas</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <BusinessCenter :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Business Center</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ParkingSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Estacionamiento Privado</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <TransportSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Transporte Aeropuerto-Hotel</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <RestaurantSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Restaurante en el 5to. piso UMO Penthouse</p>
                            </li>
                        </ul>
                        <a href="/#reservar" class="w-fit text-white bg-nuve-hoteles-blue font-semibold px-10 py-2">Reservar ahora</a>
                    </div>
                </div>
            </div>
            <div class="w-full grid grid-cols-5 gap-3">
                <template v-for="(src, i) in images_torreon">
                    <el-image
                    class=" flex-1 w-full h-24 md:h-52"
                    :src="src"
                    :zoom-rate="1.2"
                    :max-scale="7"
                    :min-scale="0.2"
                    :preview-src-list="images_torreon"
                    show-progress
                    :initial-index="i"
                    fit="cover"
                    />
                </template>
            </div>
        </div>
    </section>

    <section class="relative h-[400px] bg-[url(/img/hotels-12.webp)] bg-cover bg-center bg-fixed flex items-center justify-center text-white mt-20 px-2">
        <div class="absolute inset-0 bg-[#172a3a]/50"></div>
        <div class="max-w-3xl mx-auto flex flex-col gap-3 items-center justify-center text-center z-[1]">
            <h2 class="uppercase font-bold text-2xl md:text-5xl">Reserva tu habitación en menos de 5 minutos</h2>
            <span>Representamos la hospitalidad de la Comarca Lagunera haciendo de su estancia una experiencia única.</span>
            <a href="" class="w-fit text-white bg-transparent border border-white font-semibold px-10 py-2 rounded-sm">Nuestros hoteles</a>
        </div>
    </section>

    <section class="max-w-6xl mx-auto mt-20 px-2">
        <div class="flex flex-col gap-10">
            <div class="flex flex-col md:flex-row gap-10">
                <div class="w-full md:w-1/2 flex flex-col items-center md:items-start justify-around">
                    <span class="text-xs text-nuve-hoteles-blue font-semibold">Desde $1,750/noche</span>
                    <div class="flex flex-col gap-3 mt-5">
                        <h2 class="uppercase font-bold text-2xl md:text-5xl text-center md:text-start">Gómez Palacio, Dgo.</h2>
                        <ul class="flex flex-col gap-4 [&>li]:text-sm [&>li]:text-gray-800">
                            <li class="flex items-center gap-3">
                                <PinSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Blvd. Miguel Alemán 251, Las Rosas, 35090 Gómez Palacio, Dgo.</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-in 15.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-out 12.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <WifiSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Wi-Fi</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <BoardRoom :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Salas de Juntas</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <BusinessCenter :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Business Center</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ParkingSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Estacionamiento Privado</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <TransportSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Transporte Aeropuerto-Hotel</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <RestaurantSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>UMO Penthouse</p>
                            </li>
                        </ul>
                        <a href="/#reservar" class="w-fit text-white bg-nuve-hoteles-blue font-semibold px-10 py-2">Reservar ahora</a>
                    </div>
                </div>
                <img class="w-full md:w-1/2 h-auto object-cover" src="/img/hotels-13.webp" alt="">
            </div>
            <div class="w-full grid grid-cols-5 gap-3">
                <template v-for="(src, i) in images_gomez">
                    <el-image
                    class=" flex-1 w-full h-24 md:h-52"
                    :src="src"
                    :zoom-rate="1.2"
                    :max-scale="7"
                    :min-scale="0.2"
                    :preview-src-list="images_gomez"
                    show-progress
                    :initial-index="i"
                    fit="cover"
                    />
                </template>
            </div>
        </div>
    </section>

    <section class="max-w-6xl mx-auto mt-20 px-2">
        <div class="flex flex-col gap-10">
            <div class="flex flex-col-reverse md:flex-row gap-10">
                <img class="w-full md:w-1/2 h-auto object-cover" src="/img/hotels-38.webp" alt="">
                <div class="w-full md:w-1/2 flex flex-col items-center md:items-start justify-around">
                    <span class="text-xs text-nuve-hoteles-blue font-semibold">Desde $1,750/noche</span>
                    <div class="flex flex-col gap-3 mt-5">
                        <h2 class="uppercase font-bold text-2xl md:text-5xl text-center md:text-start">NUVE Parras</h2>
                        <ul class="flex flex-col gap-4 [&>li]:text-sm [&>li]:text-gray-800">
                            <li class="flex items-center gap-3">
                                <PinSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Vicente Guerrero 27, Zona Centro. Parras, Coah.</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-in 15.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ClockSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Check-out 12.00 hrs</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <WifiSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Wi-Fi</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <BoardRoom :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Salas de Juntas</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <ParkingSvg2 :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>Estacionamiento Privado</p>
                            </li>
                            <li class="flex items-center gap-3">
                                <RestaurantSvg :width="20" :height="20" :class="'text-nuve-hoteles-blue'" />
                                <p>UMO Penthouse</p>
                            </li>
                        </ul>
                        <a href="/#reservar" class="w-fit text-white bg-nuve-hoteles-blue font-semibold px-10 py-2">Reservar ahora</a>
                    </div>
                </div>
            </div>
            <div class="w-full grid grid-cols-5 gap-3">
                <template v-for="(src, i) in images_parras">
                    <el-image
                    class=" flex-1 w-full h-24 md:h-52"
                    :src="src"
                    :zoom-rate="1.2"
                    :max-scale="7"
                    :min-scale="0.2"
                    :preview-src-list="images_parras"
                    show-progress
                    :initial-index="i"
                    fit="cover"
                    />
                </template>
            </div>
        </div>
    </section>

    <section class="relative h-[400px] bg-[url(/img/hotels-12.webp)] bg-cover bg-center bg-fixed flex items-center justify-center text-white mt-20 px-2">
        <div class="absolute inset-0 bg-[#172a3a]/50"></div>
        <div class="max-w-3xl mx-auto flex flex-col gap-3 items-center justify-center text-center z-[1]">
            <h2 class="uppercase font-bold text-2xl md:text-5xl">Reserva tu habitación en menos de 5 minutos</h2>
            <span>Representamos la hospitalidad de la Comarca Lagunera haciendo de su estancia una experiencia única.</span>
            <a href="/#reservar" class="w-fit text-white bg-transparent border border-white font-semibold px-10 py-2 rounded-sm">Reservar ahora</a>
        </div>
    </section>
    
    <Footer />
</template>

<script>
import ArrowRightSvg from '@/Components/ArrowRightSvg.vue';
import BoardRoom from '@/Components/BoardRoom.vue';
import BookNowButton from '@/Components/BookNowButton.vue';
import BusinessCenter from '@/Components/BusinessCenter.vue';
import ClockSvg from '@/Components/ClockSvg.vue';
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import Parking from '@/Components/ParkingSvg2.vue';
import ParkingSvg from '@/Components/ParkingSvg.vue';
import PinAddressSvg from '@/Components/PinAddressSvg.vue';
import PinAddressSvgFilled from '@/Components/PinAddressSvgFilled.vue';
import RestaurantSvg from '@/Components/RestaurantSvg.vue';
import TransportSvg from '@/Components/TransportSvg.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';
import WifiSvg from '@/Components/WifiSvg.vue';
import { Head } from '@inertiajs/vue3';
import ParkingSvg2 from '@/Components/ParkingSvg2.vue';
import ClockSvg2 from '@/Components/ClockSvg2.vue';
import PinSvg2 from '@/Components/PinSvg2.vue';

export default {
    components: {
        Head,
        Header,
        ArrowRightSvg,
        WhatsappButton,
        BookNowButton,
        PinAddressSvgFilled,
        ClockSvg2,
        WifiSvg,
        BoardRoom,
        BusinessCenter,
        ParkingSvg2,
        TransportSvg,
        RestaurantSvg,
        PinSvg2,
        Footer,
    },

    data() {
        return {
            activeIndex: 0,
            images_torreon: [
                '/img/hotels-2.webp',
                '/img/hotels-49.webp',
                '/img/hotels-50.webp',
                '/img/hotels-5.webp',
                '/img/hotels-6.webp',
                '/img/hotels-7.webp',
                '/img/home-8.webp',
                '/img/hotels-9.webp',
                '/img/hotels-10.webp',
                '/img/hotels-11.webp',
            ],
            images_gomez: [
                '/img/hotels-63.webp',
                '/img/hotels-47.webp',
                '/img/hotels-53.webp',
                '/img/hotels-43.webp',
                '/img/hotels-44.webp',
                '/img/hotels-52.webp',
                '/img/hotels-48.webp',
                '/img/hotels-39.webp',
                '/img/hotels-40.webp',
                '/img/hotels-41.webp',
                '/img/hotels-42.webp',
                '/img/hotels-54.webp',
                '/img/hotels-55.webp',
                '/img/hotels-56.webp',
                '/img/hotels-57.webp',
                '/img/hotels-58.webp',
                '/img/hotels-59.webp',
                '/img/hotels-60.webp',
                '/img/hotels-61.webp',
            ],
            images_parras: [
                '/img/hotels-19.webp',
                '/img/hotels-20.webp',
                '/img/hotels-21.webp',
                '/img/hotels-22.webp',
                '/img/hotels-23.webp',
                '/img/hotels-24.webp',
                '/img/hotels-25.webp',
                '/img/hotels-26.webp',
                '/img/hotels-27.webp',
                '/img/hotels-28.webp',
                '/img/hotels-29.webp',
                '/img/hotels-30.webp',
                '/img/hotels-31.webp',
                '/img/hotels-32.webp',
                '/img/hotels-33.webp',
                '/img/hotels-34.webp',
                '/img/hotels-35.webp',
                '/img/hotels-36.webp',
                '/img/hotels-37.webp',
            ],
        }
    }
}
</script>