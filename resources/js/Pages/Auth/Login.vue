<script setup>
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const inputClass = 'w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all';
</script>

<template>
    <Head title="Connexion" />
    <AuthLayout>
        <!-- Onglets -->
        <div class="flex border-b border-gray-200 mb-8">
            <span class="flex-1 pb-3 border-b-2 border-brand-600 text-brand-600 font-bold text-center">Connexion</span>
            <Link :href="route('register')" class="flex-1 pb-3 border-b-2 border-transparent text-gray-400 font-bold text-center hover:text-dark">Inscription</Link>
        </div>

        <h1 class="text-2xl font-black text-dark mb-1">Bon retour !</h1>
        <p class="text-gray-500 text-sm mb-6">Connectez-vous pour gérer vos annonces.</p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-xl p-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-dark mb-2">E-mail</label>
                <input v-model="form.email" type="email" required autofocus autocomplete="username" :class="inputClass" placeholder="vous@exemple.com">
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-dark">Mot de passe</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-bold text-brand-600 hover:underline">Oublié ?</Link>
                </div>
                <div class="relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" :class="inputClass + ' pr-11'" placeholder="••••••••">
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-dark">
                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" v-model="form.remember" class="rounded accent-brand-600 w-4 h-4">
                Se souvenir de moi
            </label>

            <button type="submit" :disabled="form.processing" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3.5 rounded-full font-bold shadow-floating transition-all active:scale-95 disabled:opacity-60 flex items-center justify-center gap-2">
                <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                Se connecter
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Pas encore de compte ?
            <Link :href="route('register')" class="font-bold text-brand-600 hover:underline">Créer un compte</Link>
        </p>
    </AuthLayout>
</template>
