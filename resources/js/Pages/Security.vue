<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

const RULES = [
    {
        icon: 'fa-location-dot',
        title: 'Donnez rendez-vous dans un lieu public',
        text: "Campus, marché, devant une librairie, un carrefour passant… Évitez les domiciles privés et les lieux isolés, surtout pour un premier échange.",
    },
    {
        icon: 'fa-book-open-reader',
        title: 'Vérifiez le livre avant de payer',
        text: "Comparez avec les photos de l'annonce : édition, état de la couverture, pages manquantes ou annotées. Payez seulement si le livre correspond.",
    },
    {
        icon: 'fa-comments',
        title: 'Gardez les échanges sur KABA',
        text: "La messagerie interne conserve une trace de vos accords. Un membre qui insiste pour discuter ailleurs dès le premier message doit vous alerter.",
    },
    {
        icon: 'fa-mobile-screen',
        title: 'Ne communiquez jamais vos codes',
        text: "Aucun code Mobile Money, mot de passe ou code de confirmation ne doit être partagé. KABA ne vous les demandera jamais, par aucun canal.",
    },
    {
        icon: 'fa-money-bill-wave',
        title: "Méfiez-vous des demandes d'avance",
        text: "Un vendeur sérieux n'exige pas un paiement intégral avant la remise. En cas d'envoi à distance, privilégiez un acompte modéré avec un membre bien noté.",
    },
    {
        icon: 'fa-star',
        title: 'Consultez la réputation',
        text: "Note moyenne, avis d'autres membres, nombre de ventes, badge « Vérifié » : ces éléments figurent sur chaque profil et sur la fiche du livre.",
    },
];

const FLAGS = [
    "Un prix anormalement bas pour un livre neuf ou rare.",
    "Un membre pressé, qui refuse de donner des photos supplémentaires.",
    "Une demande de paiement par un moyen inhabituel ou vers un tiers.",
    "Un message contenant un lien vers un site de paiement externe.",
    "Une orthographe et des formulations copiées d'une autre annonce.",
];
</script>

<template>
    <Head title="Sécurité des transactions" />
    <PublicLayout>
        <StaticPage
            title="Sécurité des transactions"
            subtitle="Sur KABA, les échanges se font directement entre membres, en main propre. Voici comment faire en sorte que tout se passe bien."
            icon="fa-shield-halved">

            <!-- Rappel important -->
            <div class="bg-brand-50 border border-brand-200 rounded-2xl p-6">
                <h2 class="font-black text-dark mb-2">
                    <i class="fa-solid fa-circle-info text-brand-600"></i> Comment fonctionne le paiement
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    KABA <strong>ne gère aucun paiement en ligne</strong> et ne sert pas d'intermédiaire financier.
                    Vous payez le vendeur directement, en espèces à la remise ou par Mobile Money.
                    Cela veut dire deux choses : aucune commission n'est prélevée sur votre transaction,
                    mais aussi que <strong>la vigilance vous appartient</strong>. Les conseils ci-dessous sont là pour ça.
                </p>
            </div>

            <!-- Règles -->
            <section>
                <h2 class="font-black text-dark text-xl mb-4">Les bons réflexes</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="r in RULES" :key="r.title" class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                            <i class="fa-solid" :class="r.icon"></i>
                        </div>
                        <h3 class="font-bold text-dark mb-1.5">{{ r.title }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ r.text }}</p>
                    </div>
                </div>
            </section>

            <!-- Signaux d'alerte -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6 md:p-8">
                <h2 class="font-black text-dark text-xl mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Signaux qui doivent alerter
                </h2>
                <ul class="space-y-2.5">
                    <li v-for="f in FLAGS" :key="f" class="flex gap-3 text-gray-600 text-sm">
                        <i class="fa-solid fa-circle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                        <span>{{ f }}</span>
                    </li>
                </ul>
            </section>

            <!-- Que faire en cas de problème -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6 md:p-8">
                <h2 class="font-black text-dark text-xl mb-4">En cas de problème</h2>
                <ol class="space-y-4">
                    <li class="flex gap-4">
                        <span class="w-7 h-7 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center shrink-0">1</span>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            <strong class="text-dark">Signalez l'annonce.</strong> Le bouton « Signaler » sur la fiche
                            du livre transmet le cas à notre équipe de modération, qui peut masquer l'annonce.
                        </p>
                    </li>
                    <li class="flex gap-4">
                        <span class="w-7 h-7 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center shrink-0">2</span>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            <strong class="text-dark">Laissez un avis honnête.</strong> Après une remise, votre évaluation
                            aide les prochains membres à se faire une idée juste.
                        </p>
                    </li>
                    <li class="flex gap-4">
                        <span class="w-7 h-7 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center shrink-0">3</span>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            <strong class="text-dark">Écrivez-nous.</strong> Pour un cas grave (arnaque avérée, menaces),
                            contactez-nous en détaillant les faits. En cas d'infraction, adressez-vous aussi aux
                            autorités compétentes : KABA n'a pas de pouvoir judiciaire.
                        </p>
                    </li>
                </ol>
                <Link href="/contact" class="inline-flex items-center gap-2 mt-6 bg-brand-600 hover:bg-brand-700 text-white h-11 px-5 rounded-full text-sm font-bold transition-colors">
                    <i class="fa-regular fa-envelope"></i> Signaler un cas à l'équipe
                </Link>
            </section>
        </StaticPage>
    </PublicLayout>
</template>
