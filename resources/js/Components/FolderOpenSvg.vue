<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="#fff"
        stroke="#DE803F" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-folder-open" :class="class">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path
            d="M5 19l2.757 -7.351a1 1 0 0 1 .936 -.649h12.307a1 1 0 0 1 .986 1.164l-.996 5.211a2 2 0 0 1 -1.964 1.625h-14.026a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l3 3h7a2 2 0 0 1 2 2v2" />
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