<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({ title: String });

const page = usePage();
const url = computed(() => page.url.split('?')[0]);
const admin = computed(() => page.props.auth?.user);

const NAV = [
    { href: '/admin', label: 'Tableau de bord', icon: 'fa-gauge-high', exact: true },
    { href: '/admin/signalements', label: 'Signalements', icon: 'fa-flag' },
    { href: '/admin/annonces', label: 'Annonces', icon: 'fa-book' },
    { href: '/admin/utilisateurs', label: 'Utilisateurs', icon: 'fa-users' },
    { href: '/admin/categories', label: 'Catégories', icon: 'fa-tags' },
];
const isActive = (n) => n.exact ? url.value === n.href : url.value.startsWith(n.href);
</script>

<template>
    <div class="min-h-screen flex bg-gray-50 font-sans text-dark antialiased">
        <!-- Sidebar -->
        <aside class="hidden md:flex w-60 shrink-0 bg-dark text-white flex-col sticky top-0 h-screen">
            <div class="p-5 border-b border-white/10">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/images/logo-white.png" alt="KABA" class="h-8 w-auto">
                    <div class="flex flex-col leading-none"><span class="font-black text-xl tracking-tighter">KABA</span><span class="text-[9px] font-bold text-brand-300 tracking-[0.2em] uppercase">Admin</span></div>
                </Link>
            </div>
            <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
                <Link v-for="n in NAV" :key="n.href" :href="n.href" class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 transition-colors"
                      :class="isActive(n) ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-white/10'">
                    <i class="fa-solid w-4" :class="n.icon"></i> {{ n.label }}
                </Link>
                <div class="border-t border-white/10 my-2"></div>
                <Link href="/" class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 text-gray-300 hover:bg-white/10"><i class="fa-solid fa-arrow-left w-4"></i> Retour au site</Link>
                <Link href="/logout" method="post" as="button" class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 text-gray-300 hover:bg-white/10"><i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Déconnexion</Link>
            </nav>
            <div class="p-4 border-t border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center text-sm font-black">{{ (admin?.name ?? 'A').slice(0,2).toUpperCase() }}</div>
                <div class="text-xs min-w-0"><p class="font-bold truncate">{{ admin?.name }}</p><p class="text-gray-400 truncate">{{ admin?.email }}</p></div>
            </div>
        </aside>

        <!-- Contenu -->
        <main class="flex-1 min-w-0">
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
                <h1 class="text-lg font-black text-dark">{{ title }}</h1>
                <Link href="/" class="text-sm font-bold text-gray-500 hover:text-brand-600"><i class="fa-solid fa-arrow-left text-xs"></i> Site</Link>
            </header>

            <!-- Nav mobile -->
            <nav class="md:hidden bg-dark text-white flex gap-1 px-3 py-2 overflow-x-auto text-xs font-bold">
                <Link v-for="n in NAV" :key="n.href" :href="n.href" class="px-3 py-2 rounded-lg whitespace-nowrap" :class="isActive(n) ? 'bg-brand-600' : 'text-gray-300'">{{ n.label }}</Link>
            </nav>

            <div class="p-6">
                <slot />
            </div>
        </main>
    </div>
</template>
