<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none"
        stroke="#fff" :stroke-width="stroke_width" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp" :class="class">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
        <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
    </svg>
</template>

<script>
export default {
    props: {
        width: {
            required: false,
            type: Number,
            default: 24
        },
        height: {
            required: false,
            type: Number,
            default: 24
        },
        stroke_width: {
            required: false,
            type: Number,
            default: 2
        },
        class: {
            required: false,
            type: String,
        },
    }
}
</script>