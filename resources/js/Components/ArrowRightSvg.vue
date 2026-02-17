<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" :stroke-width="stroke_width" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-right" :class="class">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 12l14 0" />
        <path d="M15 16l4 -4" />
        <path d="M15 8l4 4" />
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