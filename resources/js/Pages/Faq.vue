<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

const GROUPS = [
    {
        cat: 'Acheter un livre',
        icon: 'fa-basket-shopping',
        items: [
            {
                q: 'Comment obtenir un livre sur KABA ?',
                a: "Ouvrez la fiche du livre et cliquez sur « Ajouter au panier ». Depuis votre panier, envoyez une « demande de disponibilité » au vendeur. Il vous répond livre par livre, puis vous convenez ensemble du lieu et du moment de la remise via la messagerie.",
            },
            {
                q: 'Puis-je proposer un prix plus bas ?',
                a: "Oui. Dans le panier, chaque livre en vente a un champ « Votre offre ». Le vendeur voit votre proposition à côté du prix affiché et décide de l'accepter ou non. Le prix est un point de départ, pas une barrière.",
            },
            {
                q: 'Comment payer ?',
                a: "Le paiement se fait directement entre vous et le vendeur, en espèces à la remise ou par Mobile Money (MTN MoMo / Moov Money). KABA ne gère aucun paiement en ligne et ne prélève aucune commission.",
            },
            {
                q: 'Puis-je me faire livrer ?',
                a: "Cela dépend du vendeur. La plupart privilégient la remise en main propre, mais une livraison via Gozem ou Yango peut être organisée entre vous, à vos frais.",
            },
            {
                q: 'Le vendeur a refusé un livre de ma demande, pourquoi ?',
                a: "Un vendeur peut répondre livre par livre : il accepte ceux qui sont encore disponibles et refuse les autres (déjà vendus, réservés…). Les livres refusés apparaissent barrés et ne comptent pas dans le total.",
            },
        ],
    },
    {
        cat: 'Vendre, donner, échanger',
        icon: 'fa-tag',
        items: [
            {
                q: 'Comment publier une annonce ?',
                a: "Cliquez sur « Publier », choisissez le type (vente, don, échange ou recherche), ajoutez vos photos, le titre, l'état et le prix, puis validez. Votre annonce est visible immédiatement.",
            },
            {
                q: 'Est-ce gratuit de vendre ?',
                a: "Oui, totalement. Publier une annonce et vendre est gratuit, sans commission sur vos ventes.",
            },
            {
                q: 'Combien de photos puis-je ajouter ?',
                a: "Jusqu'à 10 photos par annonce. Montrez la couverture, la tranche et surtout les défauts éventuels (pages cornées, annotations) : les annonces transparentes se vendent mieux et évitent les mauvaises surprises à la remise.",
            },
            {
                q: "J'ai plusieurs exemplaires du même livre",
                a: "Indiquez la quantité dans votre annonce. À chaque remise confirmée, un exemplaire est décompté automatiquement, et l'annonce ne passe en « vendu » qu'une fois le stock épuisé.",
            },
            {
                q: 'Comment donner un livre ?',
                a: "Choisissez le type « Don » à la publication. Le livre apparaît alors gratuitement dans les dons solidaires, avec un badge orange.",
            },
            {
                q: "Comment fonctionne l'échange ?",
                a: "Publiez votre livre en « Échange » et indiquez ce que vous recherchez. Un autre membre peut vous proposer un troc via la messagerie.",
            },
            {
                q: "Qu'est-ce qu'une annonce « Recherche » ?",
                a: "Vous cherchez un livre précis ? Publiez une annonce « Recherche ». Dès qu'un membre met ce livre en ligne, vous recevez une notification.",
            },
        ],
    },
    {
        cat: 'Confiance et sécurité',
        icon: 'fa-shield-halved',
        items: [
            {
                q: 'Comment reconnaître un vendeur fiable ?',
                a: "Consultez sa note moyenne, les avis laissés par d'autres membres, son nombre de ventes réalisées et le badge « Vérifié ». Ces informations figurent sur chaque fiche livre et sur son profil.",
            },
            {
                q: 'Comment laisser un avis ?',
                a: "Une fois la remise confirmée, acheteur et vendeur peuvent s'évaluer mutuellement depuis la page « Mes demandes » : une note sur 5 et un commentaire.",
            },
            {
                q: 'Comment signaler un problème ?',
                a: "Un bouton « Signaler » est présent sur chaque fiche livre. Vous pouvez alerter notre équipe en cas de faux livre, d'arnaque, de prix abusif ou de contenu offensant. Chaque signalement est examiné.",
            },
            {
                q: 'Comment rester en sécurité ?',
                a: "Privilégiez les remises dans des lieux publics et fréquentés, vérifiez l'état du livre avant de payer, et ne communiquez jamais vos codes Mobile Money ni vos mots de passe — KABA ne vous les demandera jamais.",
            },
        ],
    },
];

const search = ref('');
const open = ref(null); // "catIndex-itemIndex"

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return GROUPS;
    return GROUPS
        .map((g) => ({ ...g, items: g.items.filter((it) => (it.q + ' ' + it.a).toLowerCase().includes(q)) }))
        .filter((g) => g.items.length);
});

const total = computed(() => filtered.value.reduce((n, g) => n + g.items.length, 0));
function toggle(key) { open.value = open.value === key ? null : key; }
</script>

<template>
    <Head title="Centre d'aide" />
    <PublicLayout>
        <StaticPage
            title="Centre d'aide"
            subtitle="Comment pouvons-nous vous aider ? Retrouvez ici les réponses aux questions les plus fréquentes."
            icon="fa-circle-question">

            <!-- Recherche -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input v-model="search" type="search"
                       class="w-full h-13 py-3.5 pl-11 pr-4 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                       placeholder="Rechercher une question (paiement, échange, photos…)">
            </div>

            <p v-if="search" class="text-sm text-gray-500 -mt-4">
                {{ total }} résultat{{ total > 1 ? 's' : '' }}
            </p>

            <!-- Questions -->
            <section v-for="(g, gi) in filtered" :key="g.cat">
                <h2 class="flex items-center gap-2.5 font-black text-dark text-lg mb-3">
                    <i class="fa-solid text-brand-600" :class="g.icon"></i> {{ g.cat }}
                </h2>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-soft divide-y divide-gray-50 overflow-hidden">
                    <div v-for="(it, ii) in g.items" :key="it.q">
                        <button type="button" @click="toggle(`${gi}-${ii}`)"
                                class="w-full flex items-center gap-4 text-left px-5 py-4 hover:bg-gray-50 transition-colors">
                            <span class="flex-1 font-bold text-dark text-sm">{{ it.q }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform shrink-0"
                               :class="{ 'rotate-180': open === `${gi}-${ii}` }"></i>
                        </button>
                        <p v-show="open === `${gi}-${ii}`" class="px-5 pb-4 text-gray-600 text-sm leading-relaxed">
                            {{ it.a }}
                        </p>
                    </div>
                </div>
            </section>

            <div v-if="!filtered.length" class="text-center py-12 bg-white rounded-2xl border border-gray-100">
                <i class="fa-solid fa-magnifying-glass text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-500 font-medium">Aucune réponse ne correspond à « {{ search }} ».</p>
            </div>

            <!-- Contact -->
            <div class="bg-brand-50 rounded-2xl p-8 text-center">
                <h2 class="text-xl font-black text-dark mb-2">Vous ne trouvez pas votre réponse ?</h2>
                <p class="text-gray-600 mb-5">Notre équipe vous répond sous 48 heures ouvrées.</p>
                <Link href="/contact" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-12 px-6 rounded-full font-bold transition-colors">
                    <i class="fa-regular fa-envelope"></i> Nous contacter
                </Link>
            </div>
        </StaticPage>
    </PublicLayout>
</template>
