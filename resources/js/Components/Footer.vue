<template>
    <footer class="bg-white">
        <div class="max-w-[1600px] mx-auto px-6 md:px-10 py-10">
            <div class="grid gap-10 sm:grid-cols-1 lg:grid-cols-4 items-start">
                <!-- Logo -->
                <div class="w-full h-full grid place-content-center">
                    <img src="/img/logo-nuve-hoteles.webp" alt="Nuve Express" class="h-24 w-auto" />
                </div>

                <!-- Ubicaciones -->
                <div>
                    <h3 class="text-md font-semibold text-nuve-hoteles-blue tracking-wide">Hoteles</h3>
                    <ul class="mt-4 space-y-5 [&>li:hover]:text-nuve-hoteles-blue [&>li]:transition-colors [&>li]:ease-in-out">
                        <li>
                            <a href="https://goo.gl/maps/vTJhAKVWyufePM7x8" class="text-sm flex gap-2">
                                <svg class="w-5 h-5 mt-[2px] shrink-0" viewBox="0 0 24 24" fill="none"
                                    stroke="#64afdd">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" />
                                    <circle cx="12" cy="10" r="3" stroke-width="2" />
                                </svg>
                                <p>Av. Abasolo #901, Primero de Cobián Centro, Torreón, Coah. <br>(871) 717 45 95</p>
                            </a>
                        </li>
                        <li>
                            <a href="https://goo.gl/maps/5854jz5UT9v6FrQr9" class="text-sm flex gap-2">
                                <svg class="w-5 h-5 mt-[2px] shrink-0" viewBox="0 0 24 24" fill="none"
                                    stroke="#64afdd">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" />
                                    <circle cx="12" cy="10" r="3" stroke-width="2" />
                                </svg>
                                <p>Blvd. Miguel Alemán #251 col. Las Rosas Gómez Palacio, Dgo. <br>(871) 169 02 53</p>
                            </a>
                        </li>
                        <li>
                            <a href="https://maps.app.goo.gl/D4xyWQSQNU88XQmv5" class="text-sm flex gap-2">
                                <svg class="w-5 h-5 mt-[2px] shrink-0" viewBox="0 0 24 24" fill="none"
                                    stroke="#64afdd">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" />
                                    <circle cx="12" cy="10" r="3" stroke-width="2" />
                                </svg>
                                <p>Vicente Guerrero 27, Zona Centro. Parras, Coah. <br>842 115 5663 <br>842 422 1361 <br>842 115 5663</p>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Menú -->
                <div>
                    <h3 class="text-md font-semibold text-nuve-hoteles-blue tracking-wide">Menú principal</h3>
                    <ul class="mt-4 space-y-3 text-sm [&>li:hover]:text-nuve-hoteles-blue [&>li]:transition-colors [&>li]:ease-in-out">
                        <li><a href="/quienes-somos">Quiénes somos</a></li>
                        <li><a href="/nuestros-hoteles">Nuestros hoteles</a></li>
                        <li><a href="/experiencias">Experiencias</a></li>
                        <li><a href="/#reservar">Reservar ahora</a></li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div>
                    <h3 class="text-md font-semibold text-nuve-hoteles-blue tracking-wide">Información</h3>
                    <ul class="mt-4 space-y-3 text-sm [&>li:hover]:text-nuve-hoteles-blue [&>li]:transition-colors [&>li]:ease-in-out">
                        <li><a href="/nosotros">Política de Cookies</a></li>
                        <li><a href="/ubicacion">Política de Privacidad</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- (opcional) sombra/gradiente inferior suave como en la imagen -->
        <div class="flex items-center justify-center pointer-events-none h-6 w-full bg-nuve-hoteles-blue text-white text-xs font-semibold py-4">
            © 2026 GRUPO NUVE HOTELES SA DE CV. TODOS LOS DERECHOS RESERVADOS.
        </div>
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