<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    items: Array,
});

const showModal = ref(false);
const editingItem = ref(null);

const form = useForm({
    name: '',
    quantity: '',
    is_checked: false,
});

const openModal = (item = null) => {
    if (item) {
        editingItem.value = item;
        form.name = item.name;
        form.quantity = item.quantity || '';
        form.is_checked = item.is_checked;
    } else {
        editingItem.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('shopping.update', editingItem.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('shopping.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteItem = (item) => {
    if (confirm('Sei sicuro di voler eliminare questo articolo?')) {
        form.delete(route('shopping.destroy', item.id));
    }
};

const toggleCheck = (item) => {
    form.patch(route('shopping.toggle', item.id));
};
</script>

<template>
    <Head title="Lista della Spesa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Lista della Spesa
                </h2>
                <PrimaryButton @click="openModal()">
                    Aggiungi Articolo
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Articoli Non Comprati -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Da Comprare
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="item in items.filter(i => !i.is_checked)"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="item.is_checked"
                                        @change="toggleCheck(item)"
                                        class="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <div class="flex-1">
                                        <div class="text-base font-medium text-gray-900 dark:text-gray-100">
                                            {{ item.name }}
                                        </div>
                                        <div v-if="item.quantity" class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.quantity }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Aggiunto da {{ item.adder.name }}
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
                                            @click="deleteItem(item)"
                                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Elimina
                                        </button>
                                    </div>
                                </div>

                                <div v-if="items.filter(i => !i.is_checked).length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    Nessun articolo da comprare
                                </div>
                            </div>
                        </div>

                        <!-- Articoli Comprati -->
                        <div v-if="items.filter(i => i.is_checked).length > 0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Già Comprati
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="item in items.filter(i => i.is_checked)"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg opacity-60"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="item.is_checked"
                                        @change="toggleCheck(item)"
                                        class="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <div class="flex-1">
                                        <div class="text-base font-medium text-gray-900 dark:text-gray-100 line-through">
                                            {{ item.name }}
                                        </div>
                                        <div v-if="item.quantity" class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.quantity }}
                                        </div>
                                    </div>
                                    <button
                                        @click="deleteItem(item)"
                                        class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Elimina
                                    </button>
                                </div>
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
                                {{ editingItem ? 'Modifica Articolo' : 'Nuovo Articolo' }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                                    <TextInput v-model="form.name" type="text" class="mt-1 block w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantità</label>
                                    <TextInput v-model="form.quantity" type="text" placeholder="es. 2 kg, 1 confezione..." class="mt-1 block w-full" />
                                </div>

                                <div v-if="editingItem" class="flex items-center">
                                    <input
                                        v-model="form.is_checked"
                                        type="checkbox"
                                        class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        Già comprato
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <PrimaryButton type="submit" class="w-full sm:w-auto sm:ml-3" :disabled="form.processing">
                                {{ editingItem ? 'Salva' : 'Aggiungi' }}
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
