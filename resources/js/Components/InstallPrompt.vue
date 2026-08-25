<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const DISMISS_KEY = 'kaba.install.dismissed';
const DISMISS_DAYS = 14;

const deferred = ref(null); // événement Android/Chrome
const show = ref(false);
const isIos = ref(false);

function recentlyDismissed() {
    const at = Number(localStorage.getItem(DISMISS_KEY) || 0);
    return at && (Date.now() - at) < DISMISS_DAYS * 86400000;
}

function onBeforeInstall(e) {
    e.preventDefault();          // on garde la main sur le moment d'afficher
    deferred.value = e;
    if (!recentlyDismissed()) show.value = true;
}

async function install() {
    if (!deferred.value) return;
    deferred.value.prompt();
    await deferred.value.userChoice;
    deferred.value = null;
    show.value = false;
}

function dismiss() {
    localStorage.setItem(DISMISS_KEY, String(Date.now()));
    show.value = false;
}

onMounted(() => {
    // Déjà installée : rien à proposer.
    const installed = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    if (installed) return;

    window.addEventListener('beforeinstallprompt', onBeforeInstall);
    window.addEventListener('appinstalled', () => { show.value = false; });

    // iOS n'expose pas beforeinstallprompt : on explique le geste manuel.
    const ua = window.navigator.userAgent;
    if (/iPad|iPhone|iPod/.test(ua) && /Safari/.test(ua) && !/CriOS|FxiOS/.test(ua)) {
        isIos.value = true;
        if (!recentlyDismissed()) show.value = true;
    }
});

onUnmounted(() => window.removeEventListener('beforeinstallprompt', onBeforeInstall));
</script>

<template>
    <transition
        enter-active-class="transition-transform duration-300" enter-from-class="translate-y-full" enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200" leave-from-class="translate-y-0" leave-to-class="translate-y-full">
        <div v-if="show" class="fixed bottom-0 inset-x-0 z-[70] p-3 sm:p-4 pointer-events-none">
            <div class="pointer-events-auto max-w-md mx-auto bg-white rounded-2xl shadow-floating border border-gray-100 p-4 flex items-start gap-3">
                <img src="/icons/icon-192.png" alt="" class="w-12 h-12 rounded-xl shrink-0">

                <div class="flex-1 min-w-0">
                    <p class="font-black text-dark text-sm">Installer KABA</p>

                    <p v-if="isIos" class="text-gray-500 text-xs mt-0.5 leading-relaxed">
                        Appuyez sur <i class="fa-solid fa-arrow-up-from-bracket text-brand-600"></i> Partager,
                        puis « Sur l'écran d'accueil ».
                    </p>
                    <p v-else class="text-gray-500 text-xs mt-0.5 leading-relaxed">
                        Accès rapide depuis votre écran d'accueil, même avec une connexion faible.
                    </p>

                    <div v-if="!isIos" class="flex gap-2 mt-3">
                        <button @click="install"
                                class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-9 px-4 rounded-full text-xs font-bold transition-colors">
                            <i class="fa-solid fa-download"></i> Installer
                        </button>
                        <button @click="dismiss"
                                class="h-9 px-4 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">
                            Plus tard
                        </button>
                    </div>
                </div>

                <button @click="dismiss" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 shrink-0"
                        aria-label="Fermer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>
    </transition>
</template>
