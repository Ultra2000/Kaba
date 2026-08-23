<script setup>
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const CITIES = ['Cotonou', 'Abomey-Calavi', 'Porto-Novo', 'Parakou', 'Bohicon', 'Natitingou'];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    city: '',
    password: '',
    password_confirmation: '',
    cgu: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const inputClass = 'w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all';
</script>

<template>
    <Head title="Inscription" />
    <AuthLayout>
        <!-- Onglets -->
        <div class="flex border-b border-gray-200 mb-8">
            <Link :href="route('login')" class="flex-1 pb-3 border-b-2 border-transparent text-gray-400 font-bold text-center hover:text-dark">Connexion</Link>
            <span class="flex-1 pb-3 border-b-2 border-brand-600 text-brand-600 font-bold text-center">Inscription</span>
        </div>

        <h1 class="text-2xl font-black text-dark mb-1">Créer un compte</h1>
        <p class="text-gray-500 text-sm mb-6">Gratuit — rejoignez la communauté KABA.</p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-dark mb-2">Nom complet</label>
                <input v-model="form.name" type="text" required autofocus autocomplete="name" :class="inputClass" placeholder="Ex : Aïcha K.">
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-dark mb-2">E-mail</label>
                <input v-model="form.email" type="email" required autocomplete="username" :class="inputClass" placeholder="vous@exemple.com">
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-bold text-dark mb-2">Téléphone</label>
                    <input v-model="form.phone" type="tel" autocomplete="tel" :class="inputClass" placeholder="+229 ...">
                    <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-dark mb-2">Ville</label>
                    <select v-model="form.city" :class="inputClass + ' bg-white'">
                        <option value="">—</option>
                        <option v-for="c in CITIES" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <p v-if="form.errors.city" class="text-red-500 text-xs mt-1">{{ form.errors.city }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-dark mb-2">Mot de passe</label>
                <div class="relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password" :class="inputClass + ' pr-11'" placeholder="8 caractères minimum">
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-dark">
                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-dark mb-2">Confirmer le mot de passe</label>
                <input v-model="form.password_confirmation" type="password" required autocomplete="new-password" :class="inputClass" placeholder="••••••••">
            </div>

            <label class="flex items-start gap-2 text-xs text-gray-500">
                <input type="checkbox" v-model="form.cgu" class="mt-0.5 rounded accent-brand-600 w-4 h-4">
                <span>J'accepte les <a href="#" class="text-brand-600 font-bold hover:underline">CGU</a> et la <a href="#" class="text-brand-600 font-bold hover:underline">politique de confidentialité</a>.</span>
            </label>

            <button type="submit" :disabled="form.processing" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3.5 rounded-full font-bold shadow-floating transition-all active:scale-95 disabled:opacity-60 flex items-center justify-center gap-2">
                <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                Créer mon compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Déjà inscrit ?
            <Link :href="route('login')" class="font-bold text-brand-600 hover:underline">Se connecter</Link>
        </p>
    </AuthLayout>
</template>
