<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    conversations: Array,
    active: Object,
});

const messagesEl = ref(null);
const form = useForm({ body: '' });

const initials = (name) => (name || '?').split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();

function scrollToBottom() {
    nextTick(() => { if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight; });
}

function send() {
    if (!form.body.trim() || !props.active) return;
    form.post(`/messagerie/${props.active.id}`, {
        preserveScroll: true,
        onSuccess: () => { form.reset('body'); scrollToBottom(); },
    });
}

// Rafraîchissement automatique du fil actif (quasi temps réel)
let poll = null;
onMounted(() => {
    scrollToBottom();
    poll = setInterval(() => {
        if (props.active) {
            router.reload({ only: ['active', 'conversations'], preserveScroll: true, preserveState: true });
        }
    }, 4000);
});
onBeforeUnmount(() => clearInterval(poll));

watch(() => props.active?.messages?.length, scrollToBottom);
</script>

<template>
    <Head title="Messagerie" />
    <PublicLayout>
        <div class="max-w-[1200px] mx-auto px-4 py-6">
            <div class="flex bg-white rounded-2xl border border-gray-200 shadow-soft overflow-hidden" style="height:calc(100vh - 210px); min-height:520px;">

                <!-- Liste des conversations -->
                <aside class="w-full md:w-80 lg:w-96 border-r border-gray-100 flex flex-col shrink-0" :class="{ 'hidden md:flex': active }">
                    <div class="p-4 border-b border-gray-100 font-black text-dark">Messages</div>
                    <div class="flex-1 overflow-y-auto">
                        <Link v-for="c in conversations" :key="c.id" :href="`/messagerie/${c.id}`"
                              class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50"
                              :class="{ 'bg-brand-50/60': active && active.id === c.id }">
                            <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">{{ initials(c.other.name) }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-dark text-sm truncate">{{ c.other.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ c.last ?? c.listing ?? 'Nouvelle conversation' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span class="text-[11px] text-gray-400">{{ c.time }}</span>
                                <span v-if="c.unread" class="bg-brand-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ c.unread }}</span>
                            </div>
                        </Link>
                        <p v-if="!conversations.length" class="text-center text-gray-400 text-sm py-10">Aucune conversation.</p>
                    </div>
                </aside>

                <!-- Fil de discussion -->
                <section v-if="active" class="flex-1 flex flex-col min-w-0">
                    <div class="p-4 border-b border-gray-100 flex items-center gap-3">
                        <Link href="/messagerie" class="md:hidden text-gray-500 text-lg"><i class="fa-solid fa-arrow-left"></i></Link>
                        <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">{{ initials(active.other.name) }}</div>
                        <div class="flex-1 min-w-0">
                            <Link :href="`/vendeurs/${active.other.id}`" class="font-bold text-dark hover:text-brand-600">{{ active.other.name }}</Link>
                        </div>
                    </div>

                    <Link v-if="active.listing" :href="`/livres/${active.listing.id}`" class="mx-4 mt-3 flex items-center gap-3 bg-gray-50 border border-gray-100 rounded-xl p-3 hover:border-brand-300 transition-colors">
                        <i class="fa-solid fa-book text-brand-600"></i>
                        <p class="flex-1 min-w-0 font-bold text-dark text-sm truncate">{{ active.listing.title }}</p>
                        <span v-if="active.listing.type === 'vente'" class="text-brand-600 font-black text-sm">{{ new Intl.NumberFormat('fr-FR').format(active.listing.price) }} F</span>
                    </Link>

                    <div ref="messagesEl" class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="m in active.messages" :key="m.id" class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[75%]">
                                <div class="px-4 py-2.5 rounded-2xl text-sm" :class="m.mine ? 'bg-brand-600 text-white rounded-br-md' : 'bg-gray-100 text-dark rounded-bl-md'">{{ m.body }}</div>
                                <p class="text-[10px] text-gray-400 mt-1" :class="m.mine ? 'text-right pr-1' : 'pl-1'">{{ m.time }}</p>
                            </div>
                        </div>
                        <p v-if="!active.messages.length" class="text-center text-gray-400 text-sm py-8">Démarrez la conversation 👋</p>
                    </div>

                    <div class="p-4 border-t border-gray-100">
                        <form @submit.prevent="send" class="flex items-center gap-2">
                            <input v-model="form.body" type="text" class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-full text-sm outline-none focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all" placeholder="Écrivez votre message...">
                            <button type="submit" :disabled="form.processing || !form.body.trim()" class="w-11 h-11 bg-brand-600 hover:bg-brand-700 text-white rounded-full flex items-center justify-center shadow-floating transition-all active:scale-95 disabled:opacity-50 shrink-0"><i class="fa-solid fa-paper-plane"></i></button>
                        </form>
                        <p class="text-[11px] text-gray-400 text-center mt-2"><i class="fa-solid fa-lock text-[10px]"></i> Ne partagez jamais vos codes Mobile Money.</p>
                    </div>
                </section>

                <!-- État vide (desktop) -->
                <section v-else class="hidden md:flex flex-1 flex-col items-center justify-center text-gray-400">
                    <i class="fa-regular fa-comments text-6xl mb-4"></i>
                    <p class="font-medium">Sélectionnez une conversation</p>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
