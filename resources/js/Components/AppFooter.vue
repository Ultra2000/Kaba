<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-50 border-t border-gray-200 pt-16 pb-8 mt-16">
        <div class="max-w-[1400px] mx-auto px-4">
            <!-- Newsletter / App -->
            <div class="bg-dark rounded-3xl p-8 md:p-12 mb-16 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="text-white max-w-xl">
                    <h3 class="text-2xl font-bold mb-2">Rejoignez la communauté KABA</h3>
                    <p class="text-gray-400 text-sm">Téléchargez l'application pour scanner vos livres plus rapidement et recevoir des alertes sur vos recherches.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 shrink-0 w-full md:w-auto">
                    <button class="bg-white text-dark px-6 py-3 rounded-full font-bold hover:bg-gray-100 transition-colors flex items-center justify-center gap-2"><i class="fa-brands fa-apple text-xl"></i> App Store</button>
                    <button class="bg-white text-dark px-6 py-3 rounded-full font-bold hover:bg-gray-100 transition-colors flex items-center justify-center gap-2"><i class="fa-brands fa-google-play text-xl"></i> Google Play</button>
                </div>
            </div>

            <!-- Colonnes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <Link href="/" class="flex items-center gap-2 mb-6">
                        <img src="/images/logo-trans.png" alt="KABA" class="h-8 w-auto">
                        <span class="font-black text-2xl tracking-tighter text-dark">KABA</span>
                    </Link>
                    <p class="text-gray-500 text-sm mb-6">Donner une seconde vie aux livres pour rendre la culture et l'éducation accessibles à tous au Bénin.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-brand-600 text-xl transition-colors"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-brand-600 text-xl transition-colors"><i class="fa-brands fa-facebook"></i></a>
                        <a href="https://www.linkedin.com/in/fr%C3%A9jus-bouraima/" target="_blank" rel="noopener noreferrer"
                           class="text-gray-400 hover:text-brand-600 text-xl transition-colors" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-dark mb-4">La marketplace</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><Link href="/explorer" class="hover:text-brand-600 transition-colors">Vendre ses livres</Link></li>
                        <li><Link href="/explorer" class="hover:text-brand-600 transition-colors">Acheter des livres</Link></li>
                        <li><Link href="/explorer?type=don" class="hover:text-brand-600 transition-colors">Dons solidaires</Link></li>
                        <li><Link href="/explorer?type=echange" class="hover:text-brand-600 transition-colors">Échanges de livres</Link></li>
                        <li><Link href="/explorer" class="hover:text-brand-600 transition-colors">Toutes les catégories</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-dark mb-4">À propos</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><Link href="/a-propos" class="hover:text-brand-600 transition-colors">Qui sommes-nous ?</Link></li>
                        <li><Link href="/a-propos#impact" class="hover:text-brand-600 transition-colors">Notre impact</Link></li>
                        <li><Link href="/explorer?type=don" class="hover:text-brand-600 transition-colors">Dons solidaires</Link></li>
                        <li><Link href="/vendeurs" class="hover:text-brand-600 transition-colors">Nos vendeurs</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-dark mb-4">Aide & Contact</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><Link href="/aide" class="hover:text-brand-600 transition-colors">Centre d'aide (FAQ)</Link></li>
                        <li><Link href="/contact" class="hover:text-brand-600 transition-colors">Nous contacter</Link></li>
                        <li><Link href="/securite" class="hover:text-brand-600 transition-colors">Sécurité des transactions</Link></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <p>© 2026 KABA Bénin. Tous droits réservés.</p>
                <div class="flex flex-wrap gap-4">
                    <Link href="/cgu" class="hover:text-dark">CGU</Link>
                    <Link href="/confidentialite" class="hover:text-dark">Politique de confidentialité</Link>
                    <Link href="/cgu#mentions" class="hover:text-dark">Mentions légales</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
