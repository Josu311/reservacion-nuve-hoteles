<template>
    <Header />

    <section class="relative h-[700px] bg-[url(/img/experiences-1.webp)] bg-cover bg-bottom bg-fixed flex flex-col items-center justify-center gap-5 text-white">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="max-w-xl mx-auto flex flex-col items-center justify-center text-center pt-32 z-[1]">
            <h2 class="uppercase font-bold text-5xl">Experiencias</h2>
        </div>
    </section>

    <section class="max-w-6xl mx-auto mt-20 px-2">
        <div class="flex flex-col-reverse md:flex-row gap-10">
            <img class="w-full md:w-[555px] h-auto" src="/img/home-8.webp" alt="">
            <div class="w-full flex flex-col items-center md:items-start justify-around">
                <span class="text-sm text-nuve-hoteles-blue font-bold">Ubicado en Hotel Nuve Torreón, coah.</span>
                <h2 class="uppercase font-bold text-2xl md:text-5xl text-center md:text-start">UMO penthouse cocktails & grill</h2>
                <p class="text-sm">Ubicados en el quinto piso del hotel, somos el primer restaurante en Torreón, Coahuila que te brinda una vista espectacular de la ciudad y una experiencia única con platillos de asador, deliciosa coctelería y la mejor selección en vinos de mesa.</p>
                <a href="https://umopenthouse.com/" class="w-fit text-white bg-nuve-hoteles-blue font-semibold px-10 py-2 uppercase">Conoce más</a>
            </div>
        </div>
        <div class="w-full flex flex-wrap gap-3 mt-3">
            <template v-for="(src, i) in images_torreon">
                <el-image
                class=" flex-1 aspect-square"
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
    </section>

    <section class="max-w-6xl mx-auto mt-20 px-2">
        <div class="flex flex-col md:flex-row gap-10">
            <div class="flex flex-col items-center md:items-start justify-around">
                <span class="text-sm text-nuve-hoteles-blue font-bold">Ubicado en Hotel Nuve Goméz Palacio, Dgo.</span>
                <h2 class="uppercase font-bold text-2xl md:text-5xl text-center md:text-start">UMO penthouse</h2>
                <p class="text-sm">Un nuevo concepto llega a Gomez Palacio: UMO Cafetería ofrece deliciosos desayunos, comidas y cenas en un concepto de cafetería con todo el sabor de UMO Penthouse. ¡Visítanos en las instalaciones del Hotel NUVE Gómez! Te esperamos.</p>
                <a href="https://umopenthouse.com/" class="w-fit text-white bg-nuve-hoteles-blue font-semibold px-10 py-2 mt-3 uppercase">Ver menú</a>
            </div>
            <img class="w-full md:w-[555px] h-auto" src="/img/hotels-53.webp" alt="">
        </div>
        <div class="w-full flex flex-wrap gap-3 mt-3">
            <template v-for="(src, i) in images_gomez">
                <el-image
                class=" flex-1 aspect-square"
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
    </section>

    <section class="relative h-[400px] bg-[url(/img/about-us-2.webp)] bg-cover bg-center bg-fixed flex items-center justify-center text-white mt-20 px-2">
        <div class="absolute inset-0 bg-[#172a3a]/50"></div>
        <div class="max-w-3xl mx-auto flex flex-col gap-3 items-center justify-center text-center z-[1]">
            <h2 class="uppercase font-bold text-2xl md:text-5xl">Reserva tu habitación en menos de 5 minutos</h2>
            <span>Representamos la hospitalidad de la Comarca Lagunera haciendo de su estancia una experiencia única.</span>
            <a href="/#reservar" class="w-fit text-white bg-transparent border border-white font-semibold uppercase px-10 py-2 mt-5 rounded-sm">Reservar ahora</a>
        </div>
    </section>

    <Footer />
</template>

<script>
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import { Head } from '@inertiajs/vue3';
import { ElImage } from 'element-plus';

export default {
    components: {
        Head,
        Header,
        Footer,
    },
    data() {
        return {
            activeIndex: 0,
            images_torreon: [
                '/img/experiences-3.webp',
                '/img/experiences-4.webp',
                '/img/experiences-5.webp',
                '/img/experiences-6.webp',
                '/img/experiences-7.webp',
            ],
            images_gomez: [
                '/img/experiences-8.webp',
                '/img/hotels-43.webp',
                '/img/hotels-48.webp',
                '/img/hotels-52.webp',
                '/img/hotels-45.webp',
            ]
        }
    }
}
</script>

<style scoped>
.gallery {
  display: grid;
  gap: 12px;
}

.main {
  width: 100%;
  height: 340px;         /* ajusta a tu diseño */
  border-radius: 12px;
  overflow: hidden;
}

.thumbs {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 120px;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.thumb {
  border: 2px solid transparent;
  border-radius: 10px;
  overflow: hidden;
  padding: 0;
  background: transparent;
  cursor: pointer;
}

.thumb :deep(img) {
  width: 100%;
  height: 78px;          /* alto miniatura */
  object-fit: cover;
  display: block;
}

.thumb.active {
  border-color: #2563eb; /* azul */
}
</style>