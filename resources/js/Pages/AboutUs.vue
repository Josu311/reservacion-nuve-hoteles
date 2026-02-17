<template>
    <section class="h-screen">

        <Head>
            <title>Nosotros | Nuve Express – Hoteles en Torreón, Coahuila</title>
            <meta name="description" content="Conoce Nuve Express, cadena de hoteles en Torreón, Coahuila. Hospedaje cómodo y accesible, ideal para viajes de trabajo y estancias largas. Reserva en línea.">
            
            <meta property="og:type" content="website" />
            <meta property="og:site_name" content="Nuve Express">
            <meta property="og:title" content="Nosotros | Nuve Express – Hoteles en Torreón"/>
            <meta property="og:description" content="Conoce Nuve Express: hoteles con ubicación estratégica en Torreón, Coahuila. Reserva en línea en minutos."/>
            <meta property="og:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png"/>
            <meta property="og:url" content="https://nuveexpress.com.mx/nosotros"/>

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Nosotros | Nuve Express – Hoteles en Torreón">
            <meta name="twitter:description" content="Conoce Nuve Express: ubicación estratégica en Torreón, Coahuila. Reserva en línea en minutos.">
            <meta property="twitter:image" content="https://nuveexpress.com.mx/img/nuve-express-og-image.png"/>

            <link rel="canonical" href="https://nuveexpress.com.mx/nosotros" />
        </Head>
        <Header />
        <section class="flex flex-col h-full">
            <div class=" relative h-screen w-full">
                <img class="w-full h-full object-cover object-top" src="/img/fachada-1.webp" alt="">
                <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
                <div
                    class="h-[90vh] flex flex-col items-center justify-end gap-5 absolute inset-0 z-10 my-auto text-white">
                    <span class="text-nuve-express-orange uppercase font-semibold">Nosotros</span>
                    <h2 class="text-4xl md:text-6xl text-center font-semibold leading-[1] uppercase">
                        Comprometidos con tu descanso</h2>
                    <BookNowButton id="reservar" />
                </div>
            </div>
        </section>
    </section>
    <section class="w-full p-3 md:p-8">
        <div class="max-w-[1600px] flex flex-col-reverse lg:flex-row items-center gap-5 mx-auto">
            <img class="w-full lg:w-[50%] h-[500px] object-cover" src="/img/fachada-2.webp" alt="">
            <div class="w-full lg:w-[50%] flex flex-col items-start justify-center gap-5 text-[22px]">
                <div class="flex flex-col gap-1">
                    <span class="text-nuve-express-orange text-lg font-semibold">Nuve Express</span>
                    <h2 class="text-4xl font-semibold uppercase">Más que un hotel, tu <br>aliado en Torreón</h2>
                </div>
                <p>Nuve Express es una cadena de hoteles ubicados en el centro de la ciudad de Torreón, Coahuila.
                </p>
                <p>Nuestra misión es brindar el mejor trato y servicio a todos nuestros huéspedes.</p>
                <p>Ven y compruébalo, somos tu mejor opción en la ciudad.</p>
                <BookNowButton class="fit-content" />
            </div>
        </div>
    </section>
    <Footer />
</template>

<script>
import BookNowButton from '@/Components/BookNowButton.vue';
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        Head,
        Header,
        BookNowButton,
        WhatsappButton,
        Footer,
    }
}
</script>