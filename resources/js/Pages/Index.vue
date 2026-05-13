<template>
    <section class="h-screen">

        <Head title="Busca tu habitación" />
        <Header />
        <section class="flex flex-col h-full">
            <div class=" relative h-[400px] w-full">
                <img class="w-full h-full object-cover" src="/img/nuvehotel-fachada.jpg" alt="">
                <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
                <div class="flex items-center justify-center absolute inset-0 z-10 p-6 text-white text-2xl font-bold">
                    Reserva aquí
                </div>
            </div>
            <div class="max-w-[80%] mx-auto">
                <el-form class="mt-4" :model="form">
                    <div class="flex flex-wrap gap-2">
                        <el-form-item class="flex-1">
                            <el-date-picker v-model="form.dateIni" type="date" placeholder="Fecha de inicio"
                                :disabled-date="disabledBeforeToday" />
                        </el-form-item>
                        <el-form-item class="flex-1">
                            <el-date-picker v-model="form.dateFin" type="date" placeholder="Fecha de salida"
                                :disabled-date="disabledBeforeStart" />
                        </el-form-item>
                        <el-form-item class="flex-1">
                            <el-select v-model="form.typeHab" placeholder="Tipo de habitación" style="width: 150px;">
                                <el-option v-for="hab in typeHabs" :key="hab.label" :label="hab.label"
                                    :value="hab.value" />
                            </el-select>
                        </el-form-item>
                        <el-form-item class="flex-1">
                            <el-input-number v-model="form.numHabs" :min="1" placeholder="Num. habitaciones" />
                        </el-form-item>
                        <el-form-item class="flex-1">
                            <el-input-number v-model="form.adults" :min="1" placeholder="Adultos" />
                        </el-form-item>
                        <el-button :disabled="enabledButton" :loading="isLoading" type="warning"
                            @click="searchHabs()">Buscar</el-button>
                    </div>
                </el-form>
            </div>
        </section>
    </section>
</template>

<script>
import Header from '@/Components/Header.vue';
import { Head, router } from '@inertiajs/vue3'
import { ElNotification } from 'element-plus';

export default {
    components: {
        Head,
        Header
    },
    data() {
        return {
            isLoading: false,
            enabledButton: false,
            form: {
                dateIni: "",
                dateFin: "",
                typeHab: "",
                numHabs: null,
                adults: null

            },

            typeHabs: [
                {
                    value: "1K",
                    label: "King size"
                },
                {
                    value: "DO",
                    label: "Doble"
                },
                {
                    value: "MK",
                    label: "Queen"
                },
                {
                    value: "SD",
                    label: "Standard"
                },
                {
                    value: "SK",
                    label: "Standard King Size"
                },
            ]
        }
    },



    methods: {
        searchHabs() {
            this.isLoading = true;

            if (this.isSameDaySearch(this.form.dateIni, this.form.dateFin)) {
                this.isLoading = false;
                ElNotification({
                    title: 'Fecha inválida',
                    message: 'No se pueden buscar habitaciones para entrada y salida dentro del mismo día.',
                    type: 'warning'
                })
                return
            }

            router.post('/disponibilidad', this.form, {
                onError: (errors) => {
                    ElNotification({
                        title: 'Error',
                        message: 'Debes llenar todos los campos del formulario',
                        type: 'error'
                    })
                },
                onFinish: () => {
                    this.isLoading = false;
                }
            })
        },
        disabledBeforeToday(date) {
            const t = new Date(); t.setHours(0, 0, 0, 0)
            return date < t
        },
        disabledBeforeStart(date) {
            const t = new Date(); t.setHours(0, 0, 0, 0)
            const start = this.form.dateIni ? new Date(this.form.dateIni) : t
            start.setHours(0, 0, 0, 0)
            return date < start
        },
        isSameDaySearch(dateIni, dateFin) {
            if (!dateIni || !dateFin) return false

            const start = new Date(dateIni)
            const end = new Date(dateFin)

            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return false

            start.setHours(0, 0, 0, 0)
            end.setHours(0, 0, 0, 0)

            return start.getTime() === end.getTime()
        },
    }
}
</script>
