<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

defineProps({ stats: Object });
</script>

<template>
    <Head title="Qui sommes-nous ?" />
    <PublicLayout>
        <StaticPage
            title="Qui sommes-nous ?"
            subtitle="KABA est une plateforme béninoise dédiée à l'achat, la vente, le don et l'échange de livres d'occasion. Notre ambition : rendre la lecture accessible tout en réduisant le gaspillage."
            icon="fa-book-open">

            <!-- Mission / Vision -->
            <div class="grid md:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h2 class="font-black text-dark text-lg mb-2">Notre mission</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Faciliter l'accès à la lecture au Bénin en donnant une seconde vie aux livres,
                        à un prix accessible, tout en soutenant une consommation responsable.
                    </p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6">
                    <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-binoculars"></i>
                    </div>
                    <h2 class="font-black text-dark text-lg mb-2">Notre vision</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Devenir la référence de l'économie circulaire du livre en Afrique francophone,
                        en commençant par faire de KABA la marketplace béninoise de la seconde main.
                    </p>
                </div>
            </div>

            <!-- L'histoire, racontée par le fondateur -->
            <section id="histoire" class="scroll-mt-24 bg-white rounded-2xl border border-gray-100 shadow-soft overflow-hidden">
                <div class="p-6 md:p-8">
                    <span class="inline-block text-brand-600 font-bold text-xs uppercase tracking-wider mb-2">Notre histoire</span>
                    <h2 class="font-black text-dark text-xl mb-5">Comment KABA est né</h2>

                    <div class="space-y-4 text-gray-600 leading-relaxed border-l-2 border-brand-100 pl-5">
                        <p>
                            Étudiant au Bénin, j'ai longtemps buté sur un problème tout bête : mettre la main
                            sur les livres dont j'avais besoin. Les bibliothèques avaient de bonnes ressources,
                            mais on empruntait avec un délai de retour — et dès qu'il fallait reconsulter un
                            ouvrage quelques semaines plus tard, il fallait y retourner. Certains titres, eux,
                            ne se trouvaient nulle part ailleurs que sur Amazon.
                        </p>
                        <p>
                            En arrivant en France, j'ai découvert autre chose : des structures solidaires et
                            des librairies d'occasion où un livre s'obtient pour quelques euros, parfois
                            gratuitement. Le contraste m'a frappé.
                        </p>
                        <p>
                            La première idée de KABA est née là : acheter des livres dans ces structures
                            solidaires et les renvoyer au pays pour les rendre accessibles.
                        </p>
                        <p class="text-dark font-medium">
                            Puis j'ai repensé à mes propres livres. Ceux que j'avais achetés, qui ne me
                            servaient plus, et qui ont fini à la poubelle. Nous sommes des milliers dans
                            ce cas. Il y avait bien plus simple que de faire venir des livres de loin :
                            relier ceux qui ont des livres qui dorment à ceux qui en cherchent, ici même.
                        </p>
                        <p>
                            C'est ce que fait KABA. Vendre, donner, échanger — peu importe la forme,
                            l'essentiel est qu'un livre déjà lu reparte entre les mains de quelqu'un,
                            au lieu d'être jeté ou racheté neuf.
                        </p>
                    </div>
                </div>

                <!-- Signature -->
                <div class="flex items-center gap-3 px-6 md:px-8 py-4 bg-gray-50/70 border-t border-gray-100">
                    <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-black text-sm shrink-0">K</span>
                    <div>
                        <p class="font-bold text-dark text-sm">Le fondateur de KABA</p>
                        <p class="text-xs text-gray-400">Cotonou · Bénin</p>
                    </div>
                </div>
            </section>

            <!-- Notre impact -->
            <section id="impact" class="scroll-mt-24">
                <h2 class="font-black text-dark text-xl mb-4">Notre impact</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5 text-center">
                        <p class="text-3xl font-black text-brand-600">{{ stats.listings }}</p>
                        <p class="text-xs text-gray-500 font-semibold uppercase mt-1">Livres en ligne</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5 text-center">
                        <p class="text-3xl font-black text-brand-600">{{ stats.members }}</p>
                        <p class="text-xs text-gray-500 font-semibold uppercase mt-1">Membres</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5 text-center">
                        <p class="text-3xl font-black text-orange-500">{{ stats.donations }}</p>
                        <p class="text-xs text-gray-500 font-semibold uppercase mt-1">Dons solidaires</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-5 text-center">
                        <p class="text-3xl font-black text-green-600">{{ stats.exchanges }}</p>
                        <p class="text-xs text-gray-500 font-semibold uppercase mt-1">Échanges proposés</p>
                    </div>
                </div>
                <p class="text-sm text-gray-400 mt-3">
                    Chiffres mis à jour en direct depuis la plateforme.
                </p>
            </section>

            <!-- Valeurs -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6 md:p-8">
                <h2 class="font-black text-dark text-xl mb-5">Ce à quoi nous tenons</h2>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <i class="fa-solid fa-hand-holding-heart text-brand-600 text-xl mt-0.5 w-6 shrink-0"></i>
                        <div>
                            <h3 class="font-bold text-dark">La solidarité avant la transaction</h3>
                            <p class="text-gray-600 text-sm">Le don et l'échange ont autant leur place que la vente. Un acheteur peut aussi proposer son prix.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <i class="fa-solid fa-shield-halved text-brand-600 text-xl mt-0.5 w-6 shrink-0"></i>
                        <div>
                            <h3 class="font-bold text-dark">La confiance</h3>
                            <p class="text-gray-600 text-sm">Avis après chaque échange, profils vérifiés, signalement en un clic et modération humaine.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <i class="fa-solid fa-recycle text-brand-600 text-xl mt-0.5 w-6 shrink-0"></i>
                        <div>
                            <h3 class="font-bold text-dark">La seconde vie</h3>
                            <p class="text-gray-600 text-sm">Chaque livre qui change de mains est un livre qui n'est ni jeté ni racheté neuf.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Appel à l'action -->
            <div class="bg-brand-600 rounded-2xl p-8 text-center">
                <h2 class="text-2xl font-black text-white mb-2">Rejoignez le mouvement</h2>
                <p class="text-brand-100 mb-6">Vendez, donnez ou trouvez votre prochain livre.</p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <Link href="/publier" class="inline-flex items-center gap-2 bg-white text-brand-700 h-12 px-6 rounded-full font-bold hover:bg-brand-50 transition-colors">
                        <i class="fa-solid fa-plus"></i> Publier un livre
                    </Link>
                    <Link href="/explorer" class="inline-flex items-center gap-2 bg-brand-700 text-white h-12 px-6 rounded-full font-bold hover:bg-brand-800 transition-colors">
                        <i class="fa-solid fa-compass"></i> Explorer le catalogue
                    </Link>
                </div>
            </div>
        </StaticPage>
    </PublicLayout>
</template>
