<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" :stroke-width="stroke_width" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin" :class="class">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
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