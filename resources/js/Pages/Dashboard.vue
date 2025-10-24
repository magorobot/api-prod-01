<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    balance: Object,
    totalUnsettled: Number,
    recentSettlements: Array,
    personalExpensesThisMonth: Number,
    openChores: Array,
    uncheckedShoppingItems: Number,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Casa In Due
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Saldo e Metriche -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Saldo da saldare -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Saldo da Saldare
                        </div>
                        <div class="mt-2">
                            <div v-if="balance" class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                                {{ formatCurrency(balance.amount) }}
                            </div>
                            <div v-else class="text-2xl font-bold text-green-600 dark:text-green-400">
                                Tutto OK!
                            </div>
                            <div v-if="balance" class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Da saldare
                            </div>
                        </div>
                    </div>

                    <!-- Spese Comuni Non Saldate -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Spese Comuni Totali
                        </div>
                        <div class="mt-2">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(totalUnsettled) }}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Non ancora saldate
                            </div>
                        </div>
                    </div>

                    <!-- Spese Personali del Mese -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Tue Spese Personali
                        </div>
                        <div class="mt-2">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(personalExpensesThisMonth) }}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Questo mese
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Faccende e Lista Spesa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Faccende Aperte -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Faccende da Fare
                                </h3>
                                <Link
                                    :href="route('chores.index')"
                                    class="text-sm text-pink-600 dark:text-pink-400 hover:text-pink-700"
                                >
                                    Vedi tutte
                                </Link>
                            </div>
                        </div>
                        <div class="p-6">
                            <div v-if="openChores.length > 0" class="space-y-4">
                                <div
                                    v-for="chore in openChores"
                                    :key="chore.id"
                                    class="flex justify-between items-start"
                                >
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ chore.title }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <span v-if="chore.assigned_user">{{ chore.assigned_user.name }}</span>
                                            <span v-if="chore.due_date"> • {{ formatDate(chore.due_date) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                Nessuna faccenda aperta
                            </div>
                        </div>
                    </div>

                    <!-- Lista della Spesa -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Lista della Spesa
                                </h3>
                                <Link
                                    :href="route('shopping.index')"
                                    class="text-sm text-pink-600 dark:text-pink-400 hover:text-pink-700"
                                >
                                    Vedi lista
                                </Link>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ uncheckedShoppingItems }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    articoli da comprare
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ultimi Saldi -->
                <div v-if="recentSettlements.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Ultimi Saldi
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="settlement in recentSettlements"
                                :key="settlement.id"
                                class="flex justify-between items-center"
                            >
                                <div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">{{ settlement.from_user.name }}</span>
                                        ha pagato
                                        <span class="font-medium">{{ settlement.to_user.name }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatDate(settlement.settled_on) }}
                                        <span v-if="settlement.note"> • {{ settlement.note }}</span>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(settlement.amount) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
