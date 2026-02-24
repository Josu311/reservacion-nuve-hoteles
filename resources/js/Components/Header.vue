<template>
    <header class="fixed top-0 left-0 w-full flex flex-col justify-center bg-white z-[997] shadow-md">
        <div class="hidden md:block w-full bg-[#1081bb] py-3">
            <div class="max-w-6xl mx-auto flex flex-col-reverse md:flex-row justify-between items-center text-white">
                <div class="flex items-center gap-5">
                    <FacebookSvg :class="'w-5'" />
                    <InstagramSvg :class="'w-5'" />
                </div>
                <div class="text-xs font-semibold">
                    <ul class="flex items-center gap-5">
                        <li class="">
                            <a href="https://goo.gl/maps/vTJhAKVWyufePM7x8" class="flex items-center gap-2">
                                <PinAddressSvg :class="'w-5'" />
                                Torreón, Coah.
                            </a>
                        </li>
                        <li class="">
                            <a href="https://goo.gl/maps/5854jz5UT9v6FrQr9" class="flex items-center gap-2">
                                <PinAddressSvg :class="'w-5'" />
                                Gómez Palacio, Dgo.
                            </a>
                        </li>
                        <li class="">
                            <a href="https://maps.app.goo.gl/D4xyWQSQNU88XQmv5" class="flex items-center gap-2">
                                <PinAddressSvg :class="'w-5'" />
                                Parras, Coah.
                            </a>
                        </li>
                    </ul>
                    <a href="" class="hidden md:flex items-center gap-2 text-xs">
                        <EmailSvg :class="'w-5'" />
                        gerencia@nuvehotel.com
                    </a>
                </div>
            </div>
        </div>
        <div class="w-full flex items-center">
            <div class="w-full max-w-6xl mx-auto flex justify-between items-center py-2 px-6 lg:px-0">
                <a href="/">
                    <img class="h-14" src="/img/logo-nuve-hoteles.webp" alt="Logo de Nuve Express">
                </a>
                <div class="hidden lg:flex items-center gap-2">
                    <ul class="flex items-center gap-12 text-xs font-semibold">
                        <li>
                            <a class="capitalize" href="/quienes-somos">Quiénes somos</a>
                        </li>
                        <li>
                            <a class="capitalize" href="/nuestros-hoteles">Nuestros hoteles</a>
                        </li>
                        <li>
                            <a class="capitalize" href="/experiencias">Experiencias</a>
                        </li>
                        <li class="flex items-center gap-2 text-lg" v-if="true">
                            <div>
                                <el-dropdown v-if="isLogged" trigger="click">
                                    <span
                                        class="el-dropdown-link flex items-center gap-2 text-lg p-3 outline outline-1 outline-gray-500 rounded-md">
                                        {{ infoUser.name }}
                                        <ArrowDown :width="14" :height="14" />
                                    </span>
                                    <template #dropdown>
                                        <el-dropdown-menu>
                                            <el-dropdown-item @click="misCompras">
                                                <ShopCartSvg :width="18" :height="18" :stroke_width="1" class="mr-2" />
                                                Mis compras
                                            </el-dropdown-item>
                                            <el-dropdown-item class="item-logout" @click="logout">
                                                <LoginSvg :width="18" :height="18" :stroke_width="1" class="mr-2" />
                                                Cerrar sesión
                                            </el-dropdown-item>
                                        </el-dropdown-menu>
                                    </template>
                                </el-dropdown>
                                <a v-else :href="route('login')" class="">
                                    <p
                                        class="flex items-center gap-1 text-sm text-white font-medium uppercase bg-nuve-hoteles-blue rounded-md ml-3 p-3 hover:underline">
                                        Iniciar sesión
                                        <LoginSvg :width="20" :height="20" :stroke_width="1" />
                                    </p>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <MenuSvg @click="isMenuOpen = !isMenuOpen" :width="30" :height="30"
                    class="relative inline-block lg:hidden hover:cursor-pointer" />
            </div>
        </div>
    </header>
    <aside
        class="fixed inset-0 lg:hidden flex flex-col justify-between w-full h-full bg-white z-[998] p-6 transition-transform ease-in-out duration-200 shadow-xl overflow-hidden"
        :class="isMenuOpen ? 'translate-x-0' : 'translate-x-full'">
        <div class="w-full flex justify-end">
            <CloseSvg @click="isMenuOpen = false" :width="30" :height="30"
                class="relative inline-block lg:hidden hover:cursor-pointer" />
        </div>

        <div class="mx-auto">
            <ul class="flex flex-col gap-12">
                <li class="flex items-center gap-2 text-lg">
                    <a class="hover:underline uppercase" href="/quienes-somos">Quiénes somos</a>
                </li>
                <li class="flex items-center gap-2 text-lg">
                    <a class="hover:underline uppercase" href="/nuestros-hoteles">Nuestros hoteles</a>
                </li>
                <li class="flex items-center gap-2 text-lg">
                    <a class="hover:underline uppercase" href="/experiencias">Experiencias</a>
                </li>
            </ul>
        </div>

        <div class="w-full flex justify-center mt-10">
            <el-dropdown v-if="isLogged" trigger="click" class="w-full">
                <span
                    class="el-dropdown-link w-full flex items-center justify-center gap-2 text-lg p-3 outline outline-1 outline-gray-500 rounded-lg">
                    {{ infoUser.name }}
                    <ArrowDown :width="14" :height="14" />
                </span>
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item @click="misCompras">
                            <ShopCartSvg :width="18" :height="18" :stroke_width="1" class="mr-2" />
                            Mis compras
                        </el-dropdown-item>
                        <el-dropdown-item @click="logout">
                            <LoginSvg :width="18" :height="18" :stroke_width="1" class="mr-2" />
                            Cerrar sesión
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>

            <a v-else :href="route('login', { redirect: $page.url })">
                <p
                    class="flex items-center gap-1 text-lg text-white font-medium uppercase bg-nuve-hoteles-blue rounded-lg ml-3 p-3 hover:underline">
                    Iniciar sesión
                    <LoginSvg :width="20" :height="20" :stroke_width="1" />
                </p>
            </a>
        </div>
    </aside>

</template>

<script>
import { router } from '@inertiajs/vue3'
import HomeSvg from './HomeSvg.vue';
import LoginSvg from './LoginSvg.vue';
import UserSvg from './UserSvg.vue';
import BuildingSvg from './BuildingSvg.vue';
import PinAddressSvg from './PinAddressSvg.vue';
import ArrowDown from './ArrowDown.vue';
import ShopCartSvg from './ShopCartSvg.vue';
import MenuSvg from './MenuSvg.vue';
import CloseSvg from './CloseSvg.vue';
import FacebookSvg from './FacebookSvg.vue';
import InstagramSvg from './InstagramSvg.vue';
import EmailSvg from './EmailSvg.vue';

export default {
    components: {
        HomeSvg,
        BuildingSvg,
        PinAddressSvg,
        LoginSvg,
        UserSvg,
        ArrowDown,
        ShopCartSvg,
        MenuSvg,
        CloseSvg,
        FacebookSvg,
        InstagramSvg,
        PinAddressSvg,
        EmailSvg,
    },

    data() {
        return {
            isMenuOpen: false,
        }
    },

    computed: {
        infoUser() {
            return (this.$page.props.auth?.user)
        },
        isLogged() {
            return (this.$page.props.auth?.check)
        }
    },

    methods: {
        login() {
            router.visit('/login');
        },
        misCompras() {
            router.visit('/mis-compras');
        },
        logout() {
            router.post(this.route('logout'));
        }
    }
}
</script>

<style>
    /* Usamos :focus porque Element Plus aplica el color de hover 
   cuando el ítem gana el foco del teclado o mouse */
.item-logout:not(.is-disabled):hover {
  background-color: #fee2e2 !important; /* Rojo clarito */
  color: #dc2626 !important;            /* Texto rojo fuerte */
}
</style>