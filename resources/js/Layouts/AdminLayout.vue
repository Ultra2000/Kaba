<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({ title: String, subtitle: String });

const page = usePage();
const url = computed(() => page.url.split('?')[0]);
const admin = computed(() => page.props.auth?.user);
const badges = computed(() => page.props.admin ?? {});
const flash = computed(() => page.props.flash?.success);

const NAV = [
    { href: '/admin', label: 'Tableau de bord', icon: 'fa-gauge-high', exact: true },
    { href: '/admin/signalements', label: 'Signalements', icon: 'fa-flag', badge: 'reports' },
    { href: '/admin/annonces', label: 'Annonces', icon: 'fa-book', badge: 'pendingListings' },
    { href: '/admin/demandes', label: 'Demandes', icon: 'fa-basket-shopping' },
    { href: '/admin/avis', label: 'Avis', icon: 'fa-star' },
    { href: '/admin/utilisateurs', label: 'Utilisateurs', icon: 'fa-users' },
    { href: '/admin/categories', label: 'Catégories', icon: 'fa-tags' },
];
const isActive = (n) => n.exact ? url.value === n.href : url.value.startsWith(n.href);
const badgeOf = (n) => n.badge ? (badges.value[n.badge] ?? 0) : 0;

const mobileNav = ref(false);
</script>

<template>
    <div class="min-h-screen flex bg-gray-50 font-sans text-dark antialiased">
        <!-- Menu latéral (bureau) -->
        <aside class="hidden lg:flex w-60 shrink-0 bg-dark text-white flex-col sticky top-0 h-screen">
            <div class="p-5 border-b border-white/10">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/images/logo-white.png" alt="KABA" class="h-8 w-auto">
                    <div class="flex flex-col leading-none">
                        <span class="font-black text-xl tracking-tighter">KABA</span>
                        <span class="text-[9px] font-bold text-brand-300 tracking-[0.2em] uppercase">Admin</span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 p-3 space-y-1 text-sm font-medium overflow-y-auto">
                <Link v-for="n in NAV" :key="n.href" :href="n.href"
                      class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 transition-colors"
                      :class="isActive(n) ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-white/10'">
                    <i class="fa-solid w-4" :class="n.icon"></i>
                    <span class="flex-1">{{ n.label }}</span>
                    <span v-if="badgeOf(n) > 0"
                          class="bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">
                        {{ badgeOf(n) }}
                    </span>
                </Link>

                <div class="border-t border-white/10 my-2"></div>
                <Link href="/" class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 text-gray-300 hover:bg-white/10">
                    <i class="fa-solid fa-arrow-left w-4"></i> Retour au site
                </Link>
                <Link href="/logout" method="post" as="button" class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 text-gray-300 hover:bg-white/10">
                    <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Déconnexion
                </Link>
            </nav>

            <div class="p-4 border-t border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center text-sm font-black shrink-0">
                    {{ (admin?.name ?? 'A').slice(0,2).toUpperCase() }}
                </div>
                <div class="text-xs min-w-0">
                    <p class="font-bold truncate">{{ admin?.name }}</p>
                    <p class="text-gray-400 truncate">{{ admin?.email }}</p>
                </div>
            </div>
        </aside>

        <!-- Contenu -->
        <main class="flex-1 min-w-0">
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex items-center gap-3 sticky top-0 z-30">
                <button @click="mobileNav = !mobileNav"
                        class="lg:hidden w-10 h-10 shrink-0 rounded-xl bg-dark text-white flex items-center justify-center"
                        aria-label="Menu">
                    <i class="fa-solid" :class="mobileNav ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="text-lg font-black text-dark truncate">{{ title }}</h1>
                    <p v-if="subtitle" class="text-xs text-gray-500 truncate">{{ subtitle }}</p>
                </div>
                <Link href="/" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-brand-600 shrink-0">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Site
                </Link>
            </header>

            <!-- Menu mobile déroulant -->
            <transition
                enter-active-class="transition duration-150" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <nav v-if="mobileNav" class="lg:hidden bg-dark text-white p-3 grid grid-cols-2 gap-1.5 text-sm font-bold">
                    <Link v-for="n in NAV" :key="n.href" :href="n.href" @click="mobileNav = false"
                          class="px-3 py-2.5 rounded-xl flex items-center gap-2"
                          :class="isActive(n) ? 'bg-brand-600' : 'bg-white/5 text-gray-300'">
                        <i class="fa-solid w-4 text-xs" :class="n.icon"></i>
                        <span class="truncate flex-1">{{ n.label }}</span>
                        <span v-if="badgeOf(n) > 0" class="bg-red-500 text-[10px] rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">
                            {{ badgeOf(n) }}
                        </span>
                    </Link>
                    <Link href="/logout" method="post" as="button" class="col-span-2 px-3 py-2.5 rounded-xl bg-white/5 text-gray-300 text-left">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-xs"></i> Déconnexion
                    </Link>
                </nav>
            </transition>

            <div class="p-4 sm:p-6">
                <!-- Confirmation d'action -->
                <transition enter-active-class="transition duration-200" enter-from-class="opacity-0 -translate-y-1">
                    <div v-if="flash" class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
                        <i class="fa-solid fa-circle-check text-green-600"></i> {{ flash }}
                    </div>
                </transition>

                <slot />
            </div>
        </main>
    </div>
</template>
