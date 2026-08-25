<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StaticPage from '@/Components/StaticPage.vue';

defineProps({ updated: String });
</script>

<template>
    <Head title="Conditions générales d'utilisation" />
    <PublicLayout>
        <StaticPage
            title="Conditions générales d'utilisation"
            subtitle="Les règles qui encadrent l'utilisation de KABA. En créant un compte, vous acceptez ces conditions."
            icon="fa-file-contract"
            :updated="updated">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6 md:p-8 space-y-7 [&_h2]:font-black [&_h2]:text-dark [&_h2]:text-lg [&_h2]:mb-2 [&_p]:text-gray-600 [&_p]:leading-relaxed [&_p]:text-sm [&_li]:text-gray-600 [&_li]:text-sm">

                <section>
                    <h2>1. Objet</h2>
                    <p>
                        KABA est une plateforme de mise en relation entre particuliers pour l'achat, la vente,
                        le don et l'échange de livres d'occasion au Bénin. KABA n'est ni vendeur, ni acheteur,
                        ni transporteur : le service se limite à mettre en relation ses membres.
                    </p>
                </section>

                <section>
                    <h2>2. Inscription</h2>
                    <p class="mb-2">
                        L'inscription est gratuite et réservée aux personnes physiques majeures, ou mineures
                        avec l'accord de leur représentant légal. Chaque membre s'engage à fournir des
                        informations exactes et à garder son mot de passe confidentiel.
                    </p>
                    <p>
                        Un compte est personnel. Toute activité effectuée depuis votre compte est réputée
                        être la vôtre.
                    </p>
                </section>

                <section>
                    <h2>3. Publication des annonces</h2>
                    <p class="mb-3">Le membre qui publie une annonce garantit qu'il est en droit de céder l'ouvrage et que sa description est sincère. Sont notamment interdits :</p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>les contrefaçons et reproductions illicites d'ouvrages ;</li>
                        <li>les contenus incitant à la haine, violents, ou contraires à la loi béninoise ;</li>
                        <li>les annonces trompeuses (état, édition ou prix volontairement faussés) ;</li>
                        <li>tout objet autre qu'un livre ou une publication assimilée ;</li>
                        <li>la publication répétée d'annonces identiques.</li>
                    </ul>
                    <p class="mt-3">
                        KABA se réserve le droit de masquer ou supprimer toute annonce non conforme,
                        et de suspendre le compte concerné.
                    </p>
                </section>

                <section>
                    <h2>4. Transactions entre membres</h2>
                    <p class="mb-2">
                        Les transactions se concluent <strong>directement entre membres</strong>. KABA ne
                        gère aucun paiement, ne perçoit aucune commission et n'assure ni le transport,
                        ni la garantie des ouvrages.
                    </p>
                    <p>
                        La demande de disponibilité, son acceptation et la confirmation de remise sont des
                        outils de suivi ; elles ne constituent pas un contrat conclu par KABA. Les litiges
                        relatifs à une transaction relèvent des seules parties concernées.
                    </p>
                </section>

                <section>
                    <h2>5. Avis et réputation</h2>
                    <p>
                        Les avis doivent refléter une expérience réelle et rester respectueux. Les avis
                        injurieux, diffamatoires, ou déposés dans le but de nuire peuvent être supprimés.
                        Il est interdit d'échanger des avis de complaisance ou de rémunérer un avis.
                    </p>
                </section>

                <section>
                    <h2>6. Responsabilité</h2>
                    <p class="mb-2">
                        KABA met en œuvre les moyens raisonnables pour assurer la disponibilité du service,
                        sans garantie d'absence d'interruption. La responsabilité de KABA ne saurait être
                        engagée du fait du comportement d'un membre, de l'état d'un ouvrage, ou d'un
                        désaccord survenu lors d'une remise.
                    </p>
                    <p>
                        Chaque membre demeure responsable des contenus qu'il publie et du respect de la
                        législation en vigueur.
                    </p>
                </section>

                <section>
                    <h2>7. Propriété intellectuelle</h2>
                    <p>
                        La marque KABA, son logo, sa charte graphique et le code de la plateforme sont
                        protégés. En publiant une photo, le membre autorise KABA à l'afficher sur la
                        plateforme dans le seul cadre de son annonce.
                    </p>
                </section>

                <section>
                    <h2>8. Données personnelles</h2>
                    <p>
                        Le traitement des données est décrit dans notre
                        <Link href="/confidentialite" class="text-brand-600 font-bold hover:underline">politique de confidentialité</Link>.
                    </p>
                </section>

                <section>
                    <h2>9. Modification et résiliation</h2>
                    <p>
                        KABA peut faire évoluer ces conditions ; les membres en sont informés sur la
                        plateforme. Tout membre peut supprimer son compte à tout moment depuis son profil.
                        KABA peut suspendre un compte en cas de manquement grave ou répété.
                    </p>
                </section>

                <section>
                    <h2>10. Droit applicable</h2>
                    <p>
                        Les présentes conditions sont soumises au droit béninois. À défaut de résolution
                        amiable, les tribunaux compétents de Cotonou sont saisis.
                    </p>
                </section>

                <section id="mentions" class="scroll-mt-24 border-t border-gray-100 pt-7">
                    <h2>Mentions légales</h2>
                    <ul class="space-y-1.5">
                        <li><strong class="text-dark">Éditeur :</strong> KABA</li>
                        <li><strong class="text-dark">Siège :</strong> Cotonou, Bénin</li>
                        <li><strong class="text-dark">Contact :</strong> contact@kaba.bj</li>
                        <li><strong class="text-dark">Directeur de la publication :</strong> le représentant légal de KABA</li>
                    </ul>
                    <p class="mt-3 text-gray-400 text-xs">
                        Les informations d'immatriculation et d'hébergement seront complétées à la mise en
                        ligne officielle de la plateforme.
                    </p>
                </section>
            </div>
        </StaticPage>
    </PublicLayout>
</template>
