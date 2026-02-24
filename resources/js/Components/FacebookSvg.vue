<template>
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :width="width" :height="height" :stroke-width="stroke_width" :class="class">
        <path
            d="M13.5 9H15V6h-1.5C11.57 6 11 7.343 11 9v2H9v3h2v7h3v-7h2.019L16 11h-2V9.5c0-.276.224-.5.5-.5z" />
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