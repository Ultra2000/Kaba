<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

const props = defineProps({ sent: Boolean });

const form = useForm({
    name: '',
    email: '',
    subject: 'question',
    message: '',
});

const SUBJECTS = {
    question: 'Une question sur le fonctionnement',
    probleme: 'Un problème avec une annonce ou un membre',
    compte: 'Mon compte',
    partenariat: 'Partenariat / presse',
    autre: 'Autre',
};

function submit() {
    form.post('/contact', { preserveScroll: true, onSuccess: () => form.reset('message') });
}
</script>

<template>
    <Head title="Nous contacter" />
    <PublicLayout>
        <StaticPage
            title="Nous contacter"
            subtitle="Une question, un souci avec une transaction, une suggestion ? Écrivez-nous, nous répondons sous 48 heures ouvrées."
            icon="fa-envelope">

            <!-- Confirmation -->
            <div v-if="sent" class="bg-green-50 border border-green-200 rounded-2xl p-5 flex gap-3">
                <i class="fa-solid fa-circle-check text-green-600 text-xl mt-0.5"></i>
                <div>
                    <p class="font-bold text-green-800">Message envoyé</p>
                    <p class="text-green-700 text-sm">Merci ! Notre équipe vous répondra à l'adresse indiquée.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-5 items-start">
                <!-- Formulaire -->
                <form @submit.prevent="submit" class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-soft p-6 space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-dark mb-1.5">Votre nom</label>
                            <input v-model="form.name" type="text" required maxlength="100"
                                   class="w-full h-11 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-1.5">Votre e-mail</label>
                            <input v-model="form.email" type="email" required maxlength="150"
                                   class="w-full h-11 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-dark mb-1.5">Sujet</label>
                        <select v-model="form.subject"
                                class="w-full h-11 px-3 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                            <option v-for="(label, key) in SUBJECTS" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-dark mb-1.5">Votre message</label>
                        <textarea v-model="form.message" rows="6" required maxlength="2000"
                                  class="w-full rounded-xl border border-gray-200 text-sm p-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                                  placeholder="Décrivez votre demande le plus précisément possible."></textarea>
                        <p v-if="form.errors.message" class="text-red-500 text-xs mt-1">{{ form.errors.message }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white h-12 px-6 rounded-full font-bold shadow-floating transition-all active:scale-95">
                        <i class="fa-solid fa-paper-plane"></i> Envoyer le message
                    </button>
                </form>

                <!-- Infos -->
                <aside class="space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5">
                        <h2 class="font-black text-dark mb-3">Autres moyens</h2>
                        <ul class="space-y-3 text-sm">
                            <li class="flex gap-3">
                                <i class="fa-solid fa-envelope text-brand-600 w-4 mt-0.5"></i>
                                <span class="text-gray-600">contact@kaba.bj</span>
                            </li>
                            <li class="flex gap-3">
                                <i class="fa-solid fa-location-dot text-brand-600 w-4 mt-0.5"></i>
                                <span class="text-gray-600">Cotonou, Bénin</span>
                            </li>
                            <li class="flex gap-3">
                                <i class="fa-solid fa-clock text-brand-600 w-4 mt-0.5"></i>
                                <span class="text-gray-600">Réponse sous 48 h ouvrées</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-brand-50 rounded-2xl p-5">
                        <h2 class="font-bold text-dark text-sm mb-2">Avant d'écrire</h2>
                        <p class="text-gray-600 text-sm mb-3">
                            La réponse à votre question s'y trouve peut-être déjà.
                        </p>
                        <Link href="/aide" class="inline-flex items-center gap-2 text-brand-700 font-bold text-sm hover:underline">
                            Consulter le centre d'aide <i class="fa-solid fa-arrow-right text-xs"></i>
                        </Link>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                        <h2 class="font-bold text-amber-900 text-sm mb-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> Signaler une annonce
                        </h2>
                        <p class="text-amber-800 text-sm">
                            Pour un problème sur une annonce précise, utilisez le bouton « Signaler »
                            présent sur sa fiche : le traitement est plus rapide.
                        </p>
                    </div>
                </aside>
            </div>
        </StaticPage>
    </PublicLayout>
</template>
