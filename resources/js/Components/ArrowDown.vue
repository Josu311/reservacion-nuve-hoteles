<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24"
        :stroke-width="stroke_width">
        <title xmlns="">arrow-down</title>
        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
            <path stroke-dasharray="20" stroke-dashoffset="20" d="M12 3l0 17.5">
                <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="20;0" />
            </path>
            <path stroke-dasharray="12" stroke-dashoffset="12" d="M12 21l7 -7M12 21l-7 -7">
                <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="12;0" />
            </path>
        </g>
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