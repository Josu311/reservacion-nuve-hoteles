<template>
    <footer class="bg-nuve-express-orange text-white">
        <div class="max-w-[1600px] mx-auto px-6 md:px-10 py-10">
            <div class="grid gap-10 sm:grid-cols-1 lg:grid-cols-4 items-start">
                <!-- Logo -->
                <div class="w-full h-full grid place-content-center">
                    <img src="/img/logo-blanco.png" alt="Nuve Express" class="h-20 w-auto" />
                </div>

                <!-- Ubicaciones -->
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">UBICACIONES</h3>
                    <ul class="mt-4 space-y-5">
                        <li>
                            <p class="font-semibold text-lg">Nuve Express Corregidora</p>
                            <a href="https://maps.app.goo.gl/jmKfHa7rXoPegSaV6" class="text-sm flex gap-2">
                                <svg class="w-4 h-4 mt-[2px] shrink-0" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" />
                                    <circle cx="12" cy="10" r="3" stroke-width="2" />
                                </svg>
                                Calle Corregidora #130, entre González Ortega y Degollado
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Menú -->
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">MENÚ</h3>
                    <ul class="mt-4 space-y-3 text-lg font-semibold">
                        <li><a href="/" class="hover:underline">Inicio</a></li>
                        <li><a href="/nosotros" class="hover:underline">Nosotros</a></li>
                        <li><a href="/ubicacion" class="hover:underline">Ubicación</a></li>
                        <li><a href="/#reservar" class="hover:underline">Reservar ahora</a></li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">CONTACTO</h3>
                    <div class="mt-4 flex items-center gap-4">
                        <a href="https://www.facebook.com/NuveExpress/" target="_blank" rel="noopener"
                            aria-label="Facebook"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 transition">
                            <!-- Facebook -->
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M13.5 9H15V6h-1.5C11.57 6 11 7.343 11 9v2H9v3h2v7h3v-7h2.019L16 11h-2V9.5c0-.276.224-.5.5-.5z" />
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/p/DRGLF2hD-Fg/" target="_blank" rel="noopener"
                            aria-label="Instagram"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 transition">
                            <!-- Instagram -->
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2" />
                                <circle cx="12" cy="12" r="4" stroke-width="2" />
                                <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" />
                            </svg>
                        </a>
                    </div>
                    <a href="https://wa.me/5214492176423"
                        class="mt-5 flex items-center gap-2 text-xl font-bold tracking-wide">
                        <WhatsappSvg />
                        +52 144-9217-6423
                    </a>
                </div>
            </div>
        </div>

        <!-- (opcional) sombra/gradiente inferior suave como en la imagen -->
        <div class="pointer-events-none h-6 w-full bg-black/10"></div>
    </footer>
</template>

<script>
import WhatsappSvg from './WhatsappSvg.vue';

export default {
    components: {
        WhatsappSvg,
    }
}
</script>