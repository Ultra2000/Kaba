<script setup>
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;
const initials = computed(() => user.name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase());
</script>

<template>
    <Head title="Mon profil" />

    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 py-10 space-y-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between gap-4 bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-2xl font-black">{{ initials }}</div>
                    <div>
                        <h1 class="text-xl font-black text-dark">{{ user.name }}</h1>
                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
                <Link :href="`/vendeurs/${user.id}`" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:underline">
                    Voir mon profil public <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                </Link>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                <UpdatePasswordForm />
            </div>

            <div class="bg-white rounded-2xl border border-red-100 p-6 sm:p-8">
                <DeleteUserForm />
            </div>
        </div>
    </PublicLayout>
</template>
