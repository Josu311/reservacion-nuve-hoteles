<template>
    <header class="fixed top-0 left-0 w-full flex justify-center bg-white z-[997]">
        <div class="w-[90%] flex justify-between items-center">
            <img src="/img/Logo-Nuve-Express-01-1.png" alt="Logo de Nuve Express" width="74.05" height="74.05">
            <div class="hidden lg:flex items-center gap-2">
                <ul class="flex items-center gap-12">
                    <li class="flex items-center gap-2 text-lg">
                        <HomeSvg :width="16" :height="16" :stroke_width="1" />
                        <a class="hover:underline uppercase" href="/">Inicio</a>
                    </li>
                    <li class="flex items-center gap-2 text-lg">
                        <BuildingSvg :width="16" :height="16" :stroke_width="1" />
                        <a class="hover:underline uppercase" href="/nosotros">Nosotros</a>
                    </li>
                    <li class="flex items-center gap-2 text-lg">
                        <PinAddressSvg :width="16" :height="16" :stroke_width="1" />
                        <a class="hover:underline uppercase" href="/hoteles">Hoteles</a>
                    </li>
                    <li class="flex items-center gap-2 text-lg" v-if="true">
                        <div>
                            <el-dropdown v-if="isLogged" trigger="click">
                                <span
                                    class="el-dropdown-link flex items-center gap-2 text-lg p-3 outline outline-1 outline-gray-500 rounded-lg">
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
                                    class="flex items-center gap-1 text-lg text-white font-medium uppercase bg-nuve-express-orange rounded-lg ml-3 p-3 hover:underline">
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
                    <HomeSvg :width="16" :height="16" :stroke_width="1" />
                    <a class="hover:underline uppercase" href="/">Inicio</a>
                </li>
                <li class="flex items-center gap-2 text-lg">
                    <BuildingSvg :width="16" :height="16" :stroke_width="1" />
                    <a class="hover:underline uppercase" href="/nosotros">Nosotros</a>
                </li>
                <li class="flex items-center gap-2 text-lg">
                    <PinAddressSvg :width="16" :height="16" :stroke_width="1" />
                    <a class="hover:underline uppercase" href="/hoteles">Hoteles</a>
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
                    class="flex items-center gap-1 text-lg text-white font-medium uppercase bg-nuve-express-orange rounded-lg ml-3 p-3 hover:underline">
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