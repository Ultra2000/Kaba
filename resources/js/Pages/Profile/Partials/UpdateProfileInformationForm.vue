<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const CITIES = ['Cotonou', 'Abomey-Calavi', 'Porto-Novo', 'Parakou', 'Bohicon', 'Natitingou'];

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone ?? '',
    city: user.city ?? '',
    bio: user.bio ?? '',
});

const inputClass = 'w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all';
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-black text-dark">Informations du profil</h2>
            <p class="mt-1 text-sm text-gray-500">Mettez à jour vos informations et votre adresse e-mail.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-5">
            <div>
                <label class="block text-sm font-bold text-dark mb-2">Nom complet</label>
                <input v-model="form.name" type="text" required autocomplete="name" :class="inputClass">
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-dark mb-2">E-mail</label>
                <input v-model="form.email" type="email" required autocomplete="username" :class="inputClass">
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
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
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-dark mb-2">Bio</label>
                <textarea v-model="form.bio" rows="3" :class="inputClass + ' resize-none'" placeholder="Présentez-vous en quelques mots..."></textarea>
                <p v-if="form.errors.bio" class="text-red-500 text-xs mt-1">{{ form.errors.bio }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-orange-50 border border-orange-200 rounded-xl p-3">
                <p class="text-sm text-orange-700">
                    Votre adresse e-mail n'est pas vérifiée.
                    <Link :href="route('verification.send')" method="post" as="button" class="font-bold text-brand-600 hover:underline">
                        Renvoyer l'e-mail de vérification.
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                    Un nouveau lien de vérification a été envoyé.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" :disabled="form.processing" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-full font-bold shadow-floating transition-all active:scale-95 disabled:opacity-60">
                    Enregistrer
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm font-medium text-green-600"><i class="fa-solid fa-check"></i> Enregistré.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
