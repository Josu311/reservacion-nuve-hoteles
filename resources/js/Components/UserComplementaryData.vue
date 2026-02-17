<template>
    <el-dialog v-model="dialogVisible" title="Información adicional" style="max-width: 500px; width: 90%;">
        <el-form ref="formRef" :model="form" :rules="rules">
            <div class="flex items-center gap-5">
                <el-form-item label="Nombre" class="w-full flex flex-col items-start" prop="name">
                    <el-input v-model="form.name" />
                </el-form-item>
                <el-form-item label="Apellido" class="w-full flex flex-col items-start" prop="lastname">
                    <el-input v-model="form.lastname" />
                </el-form-item>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-5">
                <el-form-item label="Celular" class="w-full flex flex-col items-start" prop="phone">
                    <el-input v-model="form.phone" maxlength="10" show-word-limit />
                </el-form-item>
                <el-form-item label="Correo electrónico" class="w-full flex flex-col items-start" prop="email">
                    <el-input v-model="form.email" />
                </el-form-item>
            </div>
            <div class="flex flex-col-reverse md:flex-row items-center gap-5">
                <el-form-item label="Estado" class="w-full flex flex-col items-start" prop="state">
                    <el-select v-model="form.state" placeholder="Selecciona un estado" disabled>
                        <el-option v-for="state in states" :label="state.description" :value="state.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="Ciudad" class="w-full flex flex-col items-start" prop="city">
                    <el-select v-model="form.city" placeholder="Selecciona una ciudad" disabled>
                        <el-option v-for="city in cities" :label="city.description" :value="city.id" />
                    </el-select>
                </el-form-item>
                <el-form-item @input="getAddress" label="Código postal" class="w-full flex flex-col items-start"
                    prop="cp">
                    <el-input v-model="form.cp" placeholder="Ingresa tu CP" />
                </el-form-item>
            </div>
            <el-button class="w-full mt-10" type="warning" @click="sendData" :loading="isLoading">Enviar datos</el-button>
        </el-form>
        <el-divider>
            <span class="text-gray-300">ó</span>
        </el-divider>
        <div class="flex items-center justify-center">
            <el-link href="/login">
                <span class="text-orange-400 underline">
                    Haz clic aquí para iniciar sesión
                </span>
            </el-link>
        </div>
    </el-dialog>
</template>

<script>
import axios from 'axios';
import { ElNotification } from 'element-plus';

export default {
    props: {
        visible: {
            type: Boolean,
            default: true
        },
        isPayingAtHotel: {
            type: Boolean,
            default: false
        },
    },
    emits: ['update:visible', 'send-data'],
    // Nombre, Apellido, Email, Dirección, Ciudad, Código Postal, Número de teléfono
    data() {
        return {
            states: [],
            cities: [],
            hasLoadedStates: false,
            userAddressData: {
                state: '',
                city: '',
            },
            form: {
                name: "",
                lastname: "",
                phone: "",
                email: "",
                country: "MX",
                state: null,
                city: null,
                cp: ""
            },
            rules: {
                name: [{ required: true, message: 'El nombre es obligatorio', trigger: 'blur' }],
                lastname: [{ required: true, message: 'El apellido es obligatorio', trigger: 'blur' }],
                phone: [
                    { required: true, message: 'El teléfono es obligatorio', trigger: 'blur' },
                    { pattern: /^\d{10}$/, message: 'Debe tener 10 dígitos', trigger: 'blur' },
                ],
                email: [
                    { required: true, message: 'El correo es obligatorio', trigger: ['blur', 'change'] },
                    { type: 'email', message: 'Email no válido', trigger: ['blur', 'change'] },
                ],
                country: [{ required: true, message: 'El país es obligatorio', trigger: 'blur' }],
                state: [{ required: true, message: 'El estado es obligatorio', trigger: 'blur' }],
                city: [{ required: true, message: 'La ciudad es obligatoria', trigger: 'blur' }],
                cp: [
                    { required: true, message: 'El código postal es obligatorio', trigger: 'blur' },
                    { pattern: /^\d{5}$/, message: 'Debe tener 5 dígitos', trigger: 'blur' },
                ],
            },

            isLoading: false,
        }
    },
    computed: {
        // Este es el que se usa en el v-model del <el-dialog>
        dialogVisible: {
            get() {
                return this.visible;
            },
            set(val) {
                this.$emit('update:visible', val);
            }
        }
    },
    watch: {
        // 👇 aquí controlamos CUÁNDO llamar a getStates()
        visible(newVal) {
            if (newVal && !this.hasLoadedStates) {
                this.getStates();
                this.hasLoadedStates = true;
            }
        },
        'form.state'(newVal, oldVal) {
            if (newVal !== '') {
                axios.post('/api/get-cities', {
                    region_id: this.form.state
                })
                    .then(response => {
                        if (response.status === 200) {
                            this.cities = response.data.data;
                            this.form.city = this.userAddressData.city;
                        }
                    })
                    .catch((error) => {
                        ElNotification({
                            title: 'Error',
                            message: 'Ocurrió un error al obtener las ciudades',
                            type: 'error'
                        })
                    })
            }
        },
        'form.cp'(newVal, oldVal) {
            if (newVal.length < 5) {
                this.form.state = '';
                this.form.city = '';
            }
        }

    },
    methods: {
        resetForm() {
            form = {
                name: "",
                lastname: "",
                phone: "",
                email: "",
                country: "MX",
                state: null,
                city: null,
                cp: ""
            }
        },
        getStates() {
            axios.get('/api/get-states')
                .then(response => {
                    if (response.status) {
                        this.states = response.data.data
                    }
                })
                .catch((error) => {
                    ElNotification({
                        title: 'Error',
                        message: 'Ocurrió un error al obtener los estados',
                        type: 'error'
                    })
                });
        },
        sendData() {
            this.isLoading = true;
            this.$refs.formRef.validate((valid) => {
                if (!valid) return
                this.$emit('send-data', { ...this.form });
            });

            setTimeout(() => {
                this.isLoading = false;
            }, 5000);
        },

        getAddress(event) {
            if (event.target.value.length === 5) {
                axios.post('/api/get-address', {
                    cp: this.form.cp
                })
                    .then(response => {
                        if (response.status === 200) {
                            this.userAddressData.state = response.data.region_id;
                            this.userAddressData.city = response.data.city_id;
                            this.form.state = response.data.region_id;
                        }
                    })
                    .catch((error) => {
                        ElNotification({
                            title: 'Error',
                            message: 'Ocurrió un error al obtener los datos',
                            type: 'error'
                        })
                    })
            }

        },
    }
}
</script>

<style>
.el-form-item__content {
    width: 100%;
}
</style>