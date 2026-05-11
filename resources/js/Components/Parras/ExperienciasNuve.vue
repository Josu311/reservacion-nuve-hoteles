<template>
    <section class="py-20 px-2 bg-nuve-express-beige">
        <div class="max-w-6xl mx-auto ">
            <div class="w-full mx-auto text-center">
                <span class="text-xs md:text-sm text-gray-800 font-semibold">Nuestros servicios</span>
                <h3 class="text-2xl md:text-5xl uppercase font-semibold">Experiencias Nuve</h3>
            </div>
            
            <section class="grid grid-cols-[repeat(auto-fit,minmax(1fr,1fr))] md:grid-cols-3 justify-center gap-5 mt-10 md:px-0">
                <div class="flex flex-col gap-3">
                    <img class="w-full h-72 object-cover bg-center" src="/img/hotels-28.webp" alt="">
                    <div class="flex-1 flex flex-col gap-3">
                        <p class="uppercase font-bold text-lg">UMO Parras</p>
                        <p class="text-sm flex-1">
                            Disfruta una experiencia gastronómica única en UMO Parras. Un espacio pensado para compartir, descubrir nuevos sabores y complementar tu estancia en Nuve.
                        </p>
                        <a href="/experiencias#umo-parras" class="w-fit px-6 py-2 uppercase font-semibold bg-nuve-express-blue text-white">Conoce más</a>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <img class="w-full h-72 object-cover bg-center" src="/img/hotels-33.webp" alt="">
                    <div class="flex-1 flex flex-col gap-3">
                        <p class="uppercase font-bold text-lg">Cerca del corazón de Parras</p>
                        <p class="text-sm flex-1">
                            Ubicado a pocos pasos del centro histórico, Nuve Parras te permite descubrir lo mejor del destino: arquitectura, historia, viñedos y cultura. Todo lo que hace especial a Parras está a solo minutos de tu habitación.
                        </p>
                        <a href="/experiencias#cerca-del-corazon-de-parras" class="w-fit px-6 py-2 uppercase font-semibold bg-nuve-express-blue text-white">Conoce más</a>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <img class="w-full h-72 object-cover bg-center" src="/img/parras-boda-1.webp" alt="">
                    <div class="flex-1 flex flex-col gap-3">
                        <p class="uppercase font-bold text-lg">Tu boda en Parras</p>
                        <p class="text-sm flex-1">
                            Celebra uno de los días más importantes de tu vida en el encanto de Parras. Nuve ofrece el escenario perfecto para bodas íntimas y memorables, rodeadas de historia, naturaleza y un ambiente romántico incomparable.
                        </p>
                        <a href="/experiencias#tu-boda-en-parras" class="w-fit px-6 py-2 uppercase font-semibold bg-nuve-express-blue text-white">Conoce más</a>
                    </div>
                </div>
            </section>
        </div>
    </section>
</template>

<script>
    export default {
        name: 'ExperienciasNuve',
    }
</script>