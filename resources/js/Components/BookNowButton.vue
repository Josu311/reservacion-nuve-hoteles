<template>
    <a :href="path"
        class="flex items-center justify-center bg-nuve-express-orange text-sm font-semibold uppercase p-4 text-white">
        {{ text }}
        <ArrowRightSvg :width="20" :height="20" :stroke_width="2" class="ml-2" />
    </a>
</template>

<script>
import ArrowRightSvg from './ArrowRightSvg.vue';

export default {
    props: {
        path: {
            type: String,
            required: false,
            default: '/#reservar'
        },
        text: {
            type: String,
            required: false,
            default: 'Reservar ahora'
        }
    },
    components: {
        ArrowRightSvg
    }
}
</script>