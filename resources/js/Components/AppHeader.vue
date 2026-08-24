<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const unread = computed(() => page.props.auth?.unread ?? 0);
const unreadMessages = computed(() => page.props.auth?.unreadMessages ?? 0);
const cartCount = computed(() => (page.props.auth?.cart ?? []).length);
const categories = computed(() => page.props.nav?.categories ?? []);

const open = ref(null);        // 'cat' | 'user' | null
const mobileOpen = ref(false);
const search = ref('');

function toggle(menu) { open.value = open.value === menu ? null : menu; }
function closeAll() { open.value = null; }
function onSearch() {
    router.get('/explorer', search.value ? { q: search.value } : {}, { preserveState: false });
}
function onDocClick(e) { if (!e.target.closest('[data-dd]')) closeAll(); }
onMounted(() => document.addEventListener('click', onDocClick));
onUnmounted(() => document.removeEventListener('click', onDocClick));
</script>

<template>
    <header class="bg-white sticky top-0 z-50 shadow-sm">
        <!-- Barre utilitaire -->
        <div class="bg-dark text-white text-xs">
            <div class="max-w-[1440px] mx-auto px-4 h-9 flex items-center justify-between">
                <div class="flex items-center gap-4 font-medium">
                    <span class="hidden sm:flex items-center gap-1.5 text-brand-300"><i class="fa-solid fa-truck-fast"></i> Livraison Gozem &amp; Yango au Bénin</span>
                    <span class="hidden lg:flex items-center gap-1.5 text-gray-300"><i class="fa-solid fa-shield-halved"></i> Transactions sécurisées</span>
                </div>
                <div class="flex items-center gap-4 font-medium text-gray-300">
                    <a href="#" class="hover:text-white">Aide</a>
                    <span class="hidden sm:inline">FR · FCFA</span>
                </div>
            </div>
        </div>

        <!-- Barre principale -->
        <div class="border-b border-gray-100">
            <div class="max-w-[1440px] mx-auto px-4 h-[72px] flex items-center gap-4 lg:gap-6">
                <Link href="/" class="flex items-center gap-2 shrink-0">
                    <img src="/images/logo-trans.png" alt="KABA" class="h-9 w-auto">
                    <span class="font-black text-2xl tracking-tighter text-dark">KABA</span>
                </Link>

                <!-- Catégories -->
                <div class="relative hidden lg:block" data-dd>
                    <button @click="toggle('cat')" class="flex items-center gap-2 h-11 px-4 rounded-full bg-brand-50 text-brand-700 font-bold text-sm hover:bg-brand-100 transition-colors">
                        <i class="fa-solid fa-bars-staggered"></i> Catégories
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': open === 'cat' }"></i>
                    </button>
                    <div v-show="open === 'cat'" class="absolute left-0 top-full mt-3 w-[560px] bg-white rounded-2xl shadow-soft border border-gray-100 p-4 grid grid-cols-3 gap-1">
                        <Link v-for="c in categories" :key="c.slug" :href="`/explorer?category=${c.slug}`"
                              class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                            <i class="fa-solid w-4 text-brand-500" :class="c.icon"></i> {{ c.name }}
                        </Link>
                    </div>
                </div>

                <!-- Recherche -->
                <form @submit.prevent="onSearch" class="flex-1 max-w-2xl hidden md:flex items-center">
                    <div class="flex items-center gap-2 w-full bg-gray-50 border border-gray-200 rounded-full pl-4 pr-1 py-1 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-200 transition-all">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm shrink-0"></i>
                        <input v-model="search" type="text" class="flex-1 min-w-0 h-9 bg-transparent border-0 p-0 text-sm placeholder-gray-500 font-medium outline-none focus:ring-0" placeholder="Rechercher un livre, un auteur, un ISBN...">
                        <button type="submit" class="shrink-0 h-9 w-11 bg-brand-600 text-white rounded-full flex items-center justify-center hover:bg-brand-700 transition-colors" aria-label="Rechercher"><i class="fa-solid fa-arrow-right text-sm"></i></button>
                    </div>
                </form>

                <!-- Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2 ml-auto lg:ml-0">
                    <Link href="/publier" class="hidden sm:inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-11 px-5 rounded-full text-sm font-bold shadow-floating transition-all active:scale-95">
                        <i class="fa-solid fa-plus text-xs"></i> Publier
                    </Link>
                    <Link v-if="user" href="/favoris" class="w-11 h-11 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-orange-500 transition-colors" title="Favoris">
                        <i class="fa-regular fa-heart text-lg"></i>
                    </Link>
                    <Link v-if="user" href="/panier" class="relative w-11 h-11 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-brand-600 transition-colors" title="Panier">
                        <i class="fa-solid fa-basket-shopping text-lg"></i>
                        <span v-if="cartCount > 0" class="absolute top-1.5 right-1 bg-orange-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ cartCount }}</span>
                    </Link>
                    <Link v-if="user" href="/messagerie" class="relative w-11 h-11 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-brand-600 transition-colors" title="Messages">
                        <i class="fa-regular fa-comment text-lg"></i>
                        <span v-if="unreadMessages > 0" class="absolute top-1.5 right-1.5 bg-brand-600 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ unreadMessages }}</span>
                    </Link>
                    <Link v-if="user" href="/notifications" class="relative w-11 h-11 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-brand-600 transition-colors" title="Notifications">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span v-if="unread > 0" class="absolute top-1.5 right-1.5 bg-red-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ unread }}</span>
                    </Link>

                    <!-- Connecté -->
                    <div v-if="user" class="relative" data-dd>
                        <button @click="toggle('user')" class="flex items-center gap-2 h-11 pl-1 pr-2 rounded-full hover:bg-gray-100 transition-colors">
                            <span class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-black text-sm ring-2 ring-white">{{ user.initials ?? user.name.slice(0,2).toUpperCase() }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden xl:block"></i>
                        </button>
                        <div v-show="open === 'user'" class="absolute right-0 top-full mt-3 w-56 bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="font-bold text-dark text-sm truncate">{{ user.name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                            </div>
                            <nav class="p-1.5 text-sm font-medium">
                                <Link href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-brand-50 hover:text-brand-700"><i class="fa-solid fa-gauge-high w-4"></i> Tableau de bord</Link>
                                <Link href="/favoris" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-brand-50 hover:text-brand-700"><i class="fa-solid fa-heart w-4"></i> Favoris</Link>
                                <Link href="/demandes" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-brand-50 hover:text-brand-700"><i class="fa-solid fa-basket-shopping w-4"></i> Mes demandes</Link>
                                <Link href="/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-brand-50 hover:text-brand-700"><i class="fa-solid fa-gear w-4"></i> Profil</Link>
                                <Link v-if="user.role === 'admin'" href="/admin" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-700 font-bold hover:bg-brand-50"><i class="fa-solid fa-shield-halved w-4"></i> Administration</Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <Link href="/logout" method="post" as="button" class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-500 hover:bg-gray-50"><i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Déconnexion</Link>
                            </nav>
                        </div>
                    </div>

                    <!-- Visiteur -->
                    <template v-else>
                        <Link href="/login" class="hidden sm:inline-flex items-center h-11 px-4 rounded-full text-sm font-bold text-gray-600 hover:text-brand-600">Connexion</Link>
                        <Link href="/register" class="hidden sm:inline-flex items-center h-11 px-4 rounded-full text-sm font-bold border-2 border-gray-200 text-dark hover:border-dark transition-colors">Inscription</Link>
                    </template>

                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden w-11 h-11 rounded-full flex items-center justify-center text-gray-700 hover:bg-gray-100"><i class="fa-solid fa-bars text-lg"></i></button>
                </div>
            </div>
        </div>

        <!-- Liens rapides -->
        <div class="hidden lg:block border-b border-gray-100 bg-white">
            <div class="max-w-[1440px] mx-auto px-4 h-11 flex items-center gap-7 text-sm font-medium text-gray-600">
                <Link href="/explorer" class="hover:text-brand-600 font-bold text-brand-600">Explorer</Link>
                <Link href="/vendeurs" class="hover:text-brand-600">Vendeurs</Link>
                <Link href="/explorer?category=universitaire" class="hover:text-brand-600">Universitaire</Link>
                <Link href="/explorer?category=scolaire" class="hover:text-brand-600">Scolaire</Link>
                <Link href="/explorer?category=roman" class="hover:text-brand-600">Romans</Link>
                <Link href="/explorer?type=don" class="hover:text-brand-600">Dons solidaires</Link>
            </div>
        </div>

        <!-- Menu mobile -->
        <div v-show="mobileOpen" class="lg:hidden border-b border-gray-100 bg-white">
            <div class="px-4 py-4 space-y-2">
                <form @submit.prevent="onSearch" class="relative mb-2">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input v-model="search" class="w-full h-11 pl-10 pr-4 bg-gray-50 border border-gray-200 rounded-full text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Rechercher...">
                </form>
                <Link href="/explorer" class="block px-3 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-brand-50">Explorer</Link>
                <Link href="/publier" class="block px-3 py-2.5 rounded-xl font-bold text-white bg-brand-600 text-center">Publier une annonce</Link>
                <template v-if="user">
                    <Link href="/dashboard" class="block px-3 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-brand-50">Tableau de bord</Link>
                    <Link href="/logout" method="post" as="button" class="w-full text-left px-3 py-2.5 rounded-xl font-medium text-gray-500 hover:bg-gray-50">Déconnexion</Link>
                </template>
                <template v-else>
                    <Link href="/login" class="block px-3 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-brand-50">Connexion</Link>
                    <Link href="/register" class="block px-3 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-brand-50">Inscription</Link>
                </template>
            </div>
        </div>
    </header>
</template>
