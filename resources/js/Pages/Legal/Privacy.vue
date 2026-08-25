<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

defineProps({ updated: String });

const DATA = [
    { icon: 'fa-user', label: 'Identité', detail: "Nom affiché, adresse e-mail, mot de passe chiffré. Facultatif : téléphone, ville, courte biographie, photo de profil." },
    { icon: 'fa-book', label: 'Vos annonces', detail: "Titre, auteur, état, prix, ville, photos et description des ouvrages que vous publiez." },
    { icon: 'fa-comments', label: 'Vos échanges', detail: "Messages envoyés via la messagerie interne, demandes de disponibilité, avis déposés." },
    { icon: 'fa-chart-simple', label: "Données d'usage", detail: "Pages consultées, favoris, vendeurs suivis — utilisés pour faire fonctionner le service." },
];
</script>

<template>
    <Head title="Politique de confidentialité" />
    <PublicLayout>
        <StaticPage
            title="Politique de confidentialité"
            subtitle="Quelles données nous collectons, pourquoi, et quels sont vos droits."
            icon="fa-lock"
            :updated="updated">

            <!-- Principe -->
            <div class="bg-brand-50 border border-brand-200 rounded-2xl p-6">
                <h2 class="font-black text-dark mb-2">En résumé</h2>
                <p class="text-gray-700 leading-relaxed text-sm">
                    Nous collectons le minimum nécessaire au fonctionnement de la plateforme.
                    Nous <strong>ne vendons pas vos données</strong> et ne les transmettons à aucun tiers
                    à des fins publicitaires. Vous pouvez consulter, corriger ou supprimer vos
                    informations à tout moment.
                </p>
            </div>

            <!-- Données collectées -->
            <section>
                <h2 class="font-black text-dark text-xl mb-4">Les données que nous collectons</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="d in DATA" :key="d.label" class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                            <i class="fa-solid" :class="d.icon"></i>
                        </div>
                        <h3 class="font-bold text-dark mb-1.5">{{ d.label }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ d.detail }}</p>
                    </div>
                </div>
            </section>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6 md:p-8 space-y-7 [&_h2]:font-black [&_h2]:text-dark [&_h2]:text-lg [&_h2]:mb-2 [&_p]:text-gray-600 [&_p]:leading-relaxed [&_p]:text-sm [&_li]:text-gray-600 [&_li]:text-sm">

                <section>
                    <h2>Pourquoi nous les utilisons</h2>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>créer et sécuriser votre compte ;</li>
                        <li>afficher vos annonces et permettre aux autres membres de vous contacter ;</li>
                        <li>vous envoyer les notifications liées à votre activité (demandes, messages, avis) ;</li>
                        <li>faire fonctionner la réputation (notes, avis, nombre de ventes) ;</li>
                        <li>modérer la plateforme et traiter les signalements ;</li>
                        <li>améliorer le service à partir de statistiques agrégées.</li>
                    </ul>
                </section>

                <section>
                    <h2>Ce qui est visible par les autres membres</h2>
                    <p class="mb-2">
                        Sont publics : votre nom affiché, votre ville, votre photo de profil, vos annonces
                        en ligne, votre note moyenne, les avis reçus et votre nombre de ventes.
                    </p>
                    <p>
                        Restent privés : votre adresse e-mail, votre numéro de téléphone, vos favoris,
                        votre panier et le contenu de vos messages (visible uniquement de votre interlocuteur).
                    </p>
                </section>

                <section>
                    <h2>Conservation</h2>
                    <p>
                        Vos données sont conservées tant que votre compte est actif. À sa suppression,
                        vos informations personnelles et vos annonces sont effacées. Les avis que vous avez
                        déposés peuvent être conservés de façon anonymisée, afin de ne pas fausser la
                        réputation des autres membres.
                    </p>
                </section>

                <section>
                    <h2>Sécurité</h2>
                    <p>
                        Les mots de passe sont stockés chiffrés et ne sont jamais lisibles, même par notre
                        équipe. Les échanges avec le site sont protégés par HTTPS. Aucune coordonnée
                        bancaire n'est collectée, puisque les paiements se font directement entre membres.
                    </p>
                </section>

                <section>
                    <h2>Cookies</h2>
                    <p>
                        Nous utilisons uniquement les cookies nécessaires au fonctionnement du site :
                        maintien de votre session et protection contre la falsification de requêtes.
                        Aucun cookie publicitaire ni traceur tiers n'est déposé.
                    </p>
                </section>

                <section>
                    <h2>Vos droits</h2>
                    <p class="mb-2">
                        Vous pouvez à tout moment accéder à vos données, les corriger depuis votre profil,
                        ou supprimer votre compte (et donc vos données) depuis les paramètres.
                    </p>
                    <p>
                        Pour toute demande relative à vos données, écrivez-nous à
                        <strong class="text-dark">contact@kaba.bj</strong> ou via la
                        <Link href="/contact" class="text-brand-600 font-bold hover:underline">page de contact</Link>.
                        Nous répondons sous 30 jours.
                    </p>
                </section>
            </div>
        </StaticPage>
    </PublicLayout>
</template>
