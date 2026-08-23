<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen grid lg:grid-cols-2 font-sans text-dark antialiased">
        <!-- Panneau visuel (desktop) -->
        <div class="hidden lg:flex flex-col justify-between bg-brand-600 p-12 text-white relative overflow-hidden">
            <Link href="/" class="flex items-center gap-2 relative z-10">
                <img src="/images/logo-white.png" alt="KABA" class="h-8 w-auto">
                <span class="font-black text-2xl tracking-tighter">KABA</span>
            </Link>
            <div class="relative z-10">
                <h2 class="text-4xl font-black leading-tight mb-4">Donnez une seconde vie à vos livres.</h2>
                <p class="text-brand-100 text-lg font-medium mb-8">Rejoignez la communauté qui achète, vend, donne et échange des livres partout au Bénin.</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center"><i class="fa-solid fa-tag"></i></div><span class="font-medium">Vendez vos livres en quelques minutes</span></div>
                    <div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center"><i class="fa-solid fa-hand-holding-heart"></i></div><span class="font-medium">Donnez et faites des heureux</span></div>
                    <div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center"><i class="fa-solid fa-shield-halved"></i></div><span class="font-medium">Transactions sécurisées et vérifiées</span></div>
                </div>
            </div>
            <p class="text-brand-200 text-sm relative z-10">© 2026 KABA Bénin</p>
            <i class="fa-solid fa-book-open absolute -right-10 -bottom-10 text-[20rem] text-white/5"></i>
        </div>

        <!-- Colonne formulaire -->
        <div class="flex items-center justify-center p-6 sm:p-12 bg-gray-50">
            <div class="w-full max-w-md">
                <Link href="/" class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                    <img src="/images/logo-trans.png" alt="KABA" class="h-9 w-auto">
                    <span class="font-black text-2xl tracking-tighter text-dark">KABA</span>
                </Link>
                <slot />
            </div>
        </div>
    </div>
</template>
