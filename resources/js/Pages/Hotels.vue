<template>
    <section class="h-screen">

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
        <section class="flex flex-col h-full">
            <div class=" relative h-screen w-full">
                <img class="w-full h-full object-cover object-top" src="/img/fachada-1.webp" alt="">
                <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
                <div
                    class="h-[90vh] flex flex-col items-center justify-end gap-5 absolute inset-0 z-10 my-auto text-white">
                    <span class="text-nuve-express-orange uppercase font-semibold">Hoteles</span>
                    <h2 class="text-4xl md:text-6xl text-center font-semibold leading-[1] uppercase">
                        Ubicación estratégica en Torreón</h2>
                    <BookNowButton id="reservar" />
                </div>
            </div>
        </section>
        <section class="mt-16 pb-16">

            <div
                class="max-w-[900px] grid grid-cols-1 grid-rows-1 gap-16 place-content-center mx-auto mt-10">
                <article class="max-w-[500px] lg:w-[90%] shadow-lg rounded-lg mx-auto">
                    <img class="w-full h-[300px] object-cover" src="/img/nuve-express-corregidora.png" height="300"
                        alt="">
                    <div class="flex flex-col items-center px-11 pt-5">
                        <p class="text-center text-sm font-semibold">Nuve Express <br>Corregidora</p>
                        <div class="flex items-center gap-2 mt-4 mb-6">
                            <PinAddressSvgFilled :width="20" :height="20" :fill="'#000000'" />
                            <p class="text-xs text-center">Calle Corregidora #130, entre González Ortega y Degollados
                            </p>
                        </div>
                        <BookNowButton class="fit-content -mb-6" />
                    </div>
                </article>
            </div>
        </section>
        <Footer />
    </section>
</template>

<script>
import ArrowRightSvg from '@/Components/ArrowRightSvg.vue';
import BookNowButton from '@/Components/BookNowButton.vue';
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import PinAddressSvgFilled from '@/Components/PinAddressSvgFilled.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        Head,
        Header,
        ArrowRightSvg,
        WhatsappButton,
        BookNowButton,
        PinAddressSvgFilled,
        Footer,
    }
}
</script>