<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    expenses: Array,
    settlements: Array,
    balance: Object,
    totalUnsettled: Number,
});

const activeTab = ref('common');

const showModal = ref(false);
const editingExpense = ref(null);

const form = useForm({
    type: 'common',
    amount: '',
    description: '',
    category: '',
    spent_at: new Date().toISOString().split('T')[0],
});

const categories = [
    'Alimentari',
    'Utenze',
    'Casa',
    'Trasporti',
    'Salute',
    'Tempo libero',
    'Sport',
    'Cura personale',
    'Altro',
];

const openModal = (expense = null) => {
    if (expense) {
        editingExpense.value = expense;
        form.type = expense.type;
        form.amount = expense.amount;
        form.description = expense.description;
        form.category = expense.category;
        // Converti la data nel formato corretto per l'input type="date"
        const date = new Date(expense.spent_at);
        form.spent_at = date.toISOString().split('T')[0];
    } else {
        editingExpense.value = null;
        form.reset();
        form.spent_at = new Date().toISOString().split('T')[0];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingExpense.value = null;
    form.reset();
};

const submit = () => {
    if (editingExpense.value) {
        form.put(route('expenses.update', editingExpense.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('expenses.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteExpense = (expense) => {
    if (confirm('Sei sicuro di voler eliminare questa spesa?')) {
        form.delete(route('expenses.destroy', expense.id));
    }
};

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

const filteredExpenses = computed(() => {
    return props.expenses.filter(expense => expense.type === activeTab.value);
});

// Combina spese e settlements per il tab "common"
const itemsToDisplay = computed(() => {
    if (activeTab.value === 'common') {
        // Combina spese comuni e settlements
        const expenses = filteredExpenses.value.map(e => ({
            ...e,
            item_type: 'expense',
            date: e.spent_at,
            sort_date: new Date(e.spent_at).getTime()
        }));

        const settlementsFormatted = props.settlements.map(s => ({
            ...s,
            item_type: 'settlement',
            date: s.settled_on,
            sort_date: new Date(s.settled_on).getTime()
        }));

        const combined = [...expenses, ...settlementsFormatted];

        // DEBUG
        console.log('=== ORDINAMENTO DEBUG ===');
        combined.forEach(item => {
            console.log(`[${item.item_type}] ${item.description || item.amount} | ${item.date} | sort_date: ${item.sort_date}`);
        });

        // Unisci e ordina per data (più recente prima)
        const sorted = combined.sort((a, b) => {
            return b.sort_date - a.sort_date;
        });

        console.log('=== DOPO ORDINAMENTO ===');
        sorted.forEach(item => {
            console.log(`[${item.item_type}] ${item.description || item.amount} | ${item.date} | sort_date: ${item.sort_date}`);
        });

        return sorted;
    } else {
        // Solo spese personali (ordinate per data)
        return filteredExpenses.value
            .map(e => ({
                ...e,
                item_type: 'expense',
                date: e.spent_at,
                sort_date: new Date(e.spent_at).getTime()
            }))
            .sort((a, b) => b.sort_date - a.sort_date);
    }
});

const settleBalance = () => {
    if (confirm('Vuoi saldare il conto corrente?')) {
        useForm({}).post(route('settlements.settle'));
    }
};
</script>

<template>
    <Head title="Spese" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Spese
                </h2>
                <PrimaryButton @click="openModal()">
                    Aggiungi Spesa
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Saldo Card -->
                <div v-if="activeTab === 'common'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Saldo da Saldare
                                </h3>
                                <div v-if="balance" class="mt-2">
                                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                                        {{ formatCurrency(balance.amount) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ balance.from_user.name }} deve pagare {{ balance.to_user.name }}
                                    </div>
                                </div>
                                <div v-else class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">
                                    Tutto OK!
                                </div>
                            </div>
                            <div v-if="balance">
                                <button
                                    @click="settleBalance"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                >
                                    Salda
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            Totale spese comuni non saldate: {{ formatCurrency(totalUnsettled) }}
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'common'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition',
                                    activeTab === 'common'
                                        ? 'border-pink-600 text-pink-600 dark:border-pink-400 dark:text-pink-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Spese Comuni
                            </button>
                            <button
                                @click="activeTab = 'personal'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition',
                                    activeTab === 'personal'
                                        ? 'border-pink-600 text-pink-600 dark:border-pink-400 dark:text-pink-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Spese Personali
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Spese e Saldi -->
                            <div
                                v-for="item in itemsToDisplay"
                                :key="item.item_type + '-' + item.id"
                                class="flex justify-between items-start p-4 border rounded-lg"
                                :class="item.item_type === 'settlement'
                                    ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20'
                                    : 'border-gray-200 dark:border-gray-700'"
                            >
                                <!-- Settlement -->
                                <template v-if="item.item_type === 'settlement'">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Saldo
                                            </span>
                                        </div>
                                        <div class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ item.from_user.name }} ha pagato {{ item.to_user.name }}
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(item.settled_on) }}
                                            <span v-if="item.note"> • {{ item.note }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-bold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(item.amount) }}
                                        </div>
                                    </div>
                                </template>

                                <!-- Expense -->
                                <template v-else>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="item.type === 'common' ? 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'"
                                            >
                                                {{ item.type === 'common' ? 'Comune' : 'Personale' }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ item.category }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ item.description }}
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.user.name }} • {{ formatDate(item.spent_at) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                {{ formatCurrency(item.amount) }}
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="openModal(item)"
                                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                Modifica
                                            </button>
                                            <button
                                                @click="deleteExpense(item)"
                                                class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Elimina
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div v-if="itemsToDisplay.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Nessuna spesa trovata
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submit">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                {{ editingExpense ? 'Modifica Spesa' : 'Nuova Spesa' }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                                    <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                        <option value="common">Comune</option>
                                        <option value="personal">Personale</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrizione</label>
                                    <TextInput v-model="form.description" type="text" class="mt-1 block w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</label>
                                    <select v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                        <option value="">Seleziona categoria</option>
                                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Importo (€)</label>
                                    <TextInput v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data</label>
                                    <TextInput v-model="form.spent_at" type="date" class="mt-1 block w-full" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <PrimaryButton type="submit" class="w-full sm:w-auto sm:ml-3" :disabled="form.processing">
                                {{ editingExpense ? 'Salva' : 'Aggiungi' }}
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Annulla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
