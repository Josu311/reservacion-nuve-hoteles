<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" :stroke-width="stroke_width" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-building" :class="class">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M3 21l18 0" />
        <path d="M9 8l1 0" />
        <path d="M9 12l1 0" />
        <path d="M9 16l1 0" />
        <path d="M14 8l1 0" />
        <path d="M14 12l1 0" />
        <path d="M14 16l1 0" />
        <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
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