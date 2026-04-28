<template>

    <Head title="Iniciar sesión" />

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
                <el-form-item label="Correo electrónico" class="w-full flex flex-col items-start" prop="email">
                    <el-input v-model="form.email" placeholder="ejemplo@correo.com" />
                </el-form-item>
                <el-form-item label="Contraseña" class="w-full flex flex-col items-start" prop="password">
                    <el-input v-model="form.password" type="password" placeholder="********" show-password />
                </el-form-item>
                <p class="text-end text-sm">
                    <a class="text-nuve-hoteles-blue hover:text-blue-600 hover:underline" href="/register">¿Aún no tienes
                        cuenta? ¡Regístrate
                        ahora!</a>
                </p>

                <el-button 
                    class="w-full mt-10 btn-nuve-blue" 
                    :loading="isLoading" 
                    native-type="submit"
                    @click="submit"
                >
                    Iniciar sesión
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
            form: {
                email: "",
                password: "",
            },
            rules: {
                email: [
                    { required: true, message: 'El correo es obligatorio', trigger: ['blur', 'change'] },
                    { type: 'email', message: 'Email no válido', trigger: ['blur', 'change'] },
                ],
                password: [{ required: true, message: 'La contraseña es obligatoria', trigger: 'blur' }],
            }
        }
    },
    methods: {
        resetForm() {
            form = {
                email: "",
                password: "",
            }
        },
        submit() {
            this.isLoading = true;
            axios.post(this.route('login'), this.form)
                .then(response => {
                    if (response.status === 200)
                        this.$inertia.visit(this.route('index'))
                })
                .catch((error) => {
                    ElNotification({
                        title: 'Error',
                        message: 'Ocurrió un error al iniciar sesión' + error,
                        type: 'error'
                    })
                })
                .finally(() => {
                    this.isLoading = false;
                })
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
