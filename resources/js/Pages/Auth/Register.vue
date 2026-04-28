<template>

    <Head title="Crear cuenta" />

    <!-- <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form> -->

    <section class="w-full h-[100dvh] flex items-center">
        <img class="w-[50%] h-full object-cover hidden md:block" src="/img/hotels-1.webp" alt="">
        <article class="w-full md:w-[50%] p-20">
            <img class="mx-auto" src="/img/logo-nuve-hoteles.webp" alt="Logo de Nuve Hotel" width="100px"
                height="100px">
            <el-form class="w-full" :model="form" :rules="rules" @submit.prevent="submit">
                <div class="flex items-center gap-5">
                    <el-form-item label="Nombre" class="w-full flex flex-col items-start" prop="name">
                        <el-input v-model="form.name" placeholder="Tu nombre" />
                    </el-form-item>
                    <el-form-item label="Apellido" class="w-full flex flex-col items-start" prop="lastname">
                        <el-input v-model="form.lastname" placeholder="Tu apellido" />
                    </el-form-item>
                </div>
                <div class="flex items-center gap-5">
                    <el-form-item label="Celular" class="w-full flex flex-col items-start" prop="phone">
                        <el-input v-model="form.phone" maxlength="10" show-word-limit placeholder="1234567890" />
                    </el-form-item>
                    <el-form-item label="Correo electrónico" class="w-full flex flex-col items-start" prop="email">
                        <el-input v-model="form.email" placeholder="tucorreo@email.com" />
                    </el-form-item>
                </div>
                <div class="flex items-center gap-5">
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
                <el-form-item class="w-full flex flex-col items-start" label="Contraseña" prop="password">
                    <el-input v-model="form.password" type="password" placeholder="********" show-password />
                </el-form-item>

                <el-form-item class="w-full flex flex-col items-start" label="Confirmar contraseña"
                    prop="password_confirmation">
                    <el-input v-model="form.password_confirmation" type="password" placeholder="********"
                        show-password />
                </el-form-item>
                <p class="text-end text-sm">
                    <a class="text-nuve-hoteles-blue hover:text-blue-600 hover:underline" href="/login">¿Ya tienes cuenta?
                        ¡Inicia sesión!</a>
                </p>
                <el-button 
                    class="w-full mt-10 btn-nuve-blue" 
                    :loading="isLoading" 
                    native-type="submit"
                    @click="submit"
                >
                    Crear cuenta
                </el-button>
            </el-form>
        </article>
    </section>
</template>


<script>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ElNotification } from 'element-plus';

export default {
    components: {
        Head
    },
    // Nombre, Apellido, Email, Dirección, Ciudad, Código Postal, Número de teléfono
    data() {
        return {
            isLoading: false,
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
                cp: "",
                password: "",
                password_confirmation: "",
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
                password: [{ required: true, message: 'La contraseña es obligatoria', trigger: 'blur' }],
                password_confirmation: [{ required: true, message: 'La confirmación es obligatoria', trigger: 'blur' }],
            }
        }
    },
    mounted() {
        this.getStates()
    },
    watch: {
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
                cp: "",
                password: "",
                password_confirmation: "",
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
        submit() {
            this.isLoading = true;
            axios.post(this.route('register'), {
                name: this.form.name,
                lastname: this.form.lastname,
                phone: String(this.form.phone),
                email: this.form.email,
                country: "MX",
                state: this.form.state,
                city: this.form.city,
                cp: this.form.cp,
                password: this.form.password,
                password_confirmation: this.form.password_confirmation,
            })
                .then(response => {
                    if (response.status === 200)
                        this.$inertia.visit(this.route('index'))
                })
                .catch((error) => {
                    ElNotification({
                        title: 'Error',
                        message: 'Error al intentar crear el usuario',
                        type: 'error'
                    })
                })
                .finally(() => {
                    this.isLoading = false;
                })
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

.btn-nuve-blue {
  /* Color base */
  --el-button-bg-color: #1182ba;
  --el-button-border-color: #1182ba;
  --el-button-text-color: #ffffff;

  /* Color al pasar el mouse (un poco más claro) */
  --el-button-hover-bg-color: #1a95d4;
  --el-button-hover-border-color: #1a95d4;
  --el-button-hover-text-color: #ffffff;

  /* Color al hacer clic (un poco más oscuro) */
  --el-button-active-bg-color: #0e6b9a;
  --el-button-active-border-color: #0e6b9a;
}
</style>
