<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    chores: Array,
    users: Array,
});

const showModal = ref(false);
const editingChore = ref(null);

const form = useForm({
    title: '',
    due_date: new Date().toISOString().split('T')[0],
    assigned_user_id: null,
    status: 'open',
    notes: '',
});

const openModal = (chore = null) => {
    if (chore) {
        editingChore.value = chore;
        form.title = chore.title;
        // Converti la data nel formato corretto per l'input type="date"
        const date = new Date(chore.due_date);
        form.due_date = date.toISOString().split('T')[0];
        form.assigned_user_id = chore.assigned_user_id;
        form.status = chore.status;
        form.notes = chore.notes || '';
    } else {
        editingChore.value = null;
        form.reset();
        form.due_date = new Date().toISOString().split('T')[0];
        form.status = 'open';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingChore.value = null;
    form.reset();
};

const submit = () => {
    if (editingChore.value) {
        form.put(route('chores.update', editingChore.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('chores.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteChore = (chore) => {
    if (confirm('Sei sicuro di voler eliminare questa faccenda?')) {
        form.delete(route('chores.destroy', chore.id));
    }
};

const toggleStatus = (chore) => {
    form.patch(route('chores.toggle', chore.id));
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

// Ordina le faccende per data di scadenza (più vicine prima)
const sortedChores = computed(() => {
    return [...props.chores].sort((a, b) => {
        const dateA = new Date(a.due_date).getTime();
        const dateB = new Date(b.due_date).getTime();
        return dateA - dateB;
    });
});

// Determina se una faccenda scade oggi o domani
const getDueDateBadge = (dueDate) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const due = new Date(dueDate);
    due.setHours(0, 0, 0, 0);

    if (due.getTime() === today.getTime()) {
        return 'oggi';
    } else if (due.getTime() === tomorrow.getTime()) {
        return 'domani';
    }
    return null;
};
</script>

<template>
    <Head title="Faccende" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Faccende di Casa
                </h2>
                <PrimaryButton @click="openModal()">
                    Aggiungi Faccenda
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="chore in sortedChores"
                                :key="chore.id"
                                class="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                                :class="chore.status === 'done' ? 'opacity-60' : ''"
                            >
                                <input
                                    type="checkbox"
                                    :checked="chore.status === 'done'"
                                    @change="toggleStatus(chore)"
                                    class="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="text-lg font-medium text-gray-900 dark:text-gray-100" :class="chore.status === 'done' ? 'line-through' : ''">
                                            {{ chore.title }}
                                        </div>
                                        <span
                                            v-if="getDueDateBadge(chore.due_date) && chore.status !== 'done'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="getDueDateBadge(chore.due_date) === 'oggi'
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'"
                                        >
                                            Scade {{ getDueDateBadge(chore.due_date) }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span v-if="chore.assigned_user_id === 'both'">Entrambi • </span>
                                        <span v-else-if="chore.assigned_user">{{ chore.assigned_user.name }} • </span>
                                        {{ formatDate(chore.due_date) }}
                                        <span v-if="chore.notes"> • {{ chore.notes }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="openModal(chore)"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Modifica
                                    </button>
                                    <button
                                        @click="deleteChore(chore)"
                                        class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Elimina
                                    </button>
                                </div>
                            </div>

                            <div v-if="sortedChores.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Nessuna faccenda trovata
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
                                {{ editingChore ? 'Modifica Faccenda' : 'Nuova Faccenda' }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Titolo</label>
                                    <TextInput v-model="form.title" type="text" class="mt-1 block w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Scadenza</label>
                                    <TextInput v-model="form.due_date" type="date" class="mt-1 block w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assegna a</label>
                                    <select v-model="form.assigned_user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                        <option :value="null">Nessuno</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                        <option value="both">Entrambi</option>
                                    </select>
                                </div>

                                <div v-if="editingChore">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stato</label>
                                    <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                        <option value="open">Aperta</option>
                                        <option value="done">Completata</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                                    <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <PrimaryButton type="submit" class="w-full sm:w-auto sm:ml-3" :disabled="form.processing">
                                {{ editingChore ? 'Salva' : 'Aggiungi' }}
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
