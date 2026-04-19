<template>
    <div class="pagination">
        <div class="pagination-controls">
            <div class="pagination-info">
                <span class="pagination-text">
                    Страница {{ currentPage }} из {{ lastPage }}
                </span>
            </div>

            <div class="pagination-buttons">
                <button
                    @click="prevPage"
                    :disabled="currentPage <= 1"
                    class="btn btn-primary"
                >
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <button
                    @click="nextPage"
                    :disabled="currentPage >= lastPage"
                    class="btn btn-primary"
                >
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <div class="pagination-per-page">
                <label for="perPage" class="form-label">На странице:</label>
                <select
                    id="perPage"
                    :value="props.perPage"
                    @change="handlePerPageChange"
                    class="form-select"
                >
                    <option v-for="option in perPageOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useTemplateRef } from 'vue';

interface Props {
    currentPage: number;
    lastPage: number;
    perPage: number;
    perPageOptions?: number[];
}

interface Emits {
    (e: 'update:page', value: number): void;
    (e: 'update:perPage', value: number): void;
}

const props = withDefaults(defineProps<Props>(), {
    perPageOptions: () => [9, 18, 27, 36],
});

const emit = defineEmits<Emits>();

const prevPage = (): void => {
    console.log('prevPage clicked', {
        currentPage: props.currentPage,
        lastPage: props.lastPage,
        newPage: props.currentPage - 1
    });
    if (props.currentPage > 1) {
        console.log('Emitting update:page', props.currentPage - 1);
        emit('update:page', props.currentPage - 1);
    }
};

const nextPage = (): void => {
    console.log('nextPage clicked', {
        currentPage: props.currentPage,
        lastPage: props.lastPage,
        newPage: props.currentPage + 1
    });
    if (props.currentPage < props.lastPage) {
        console.log('Emitting update:page', props.currentPage + 1);
        emit('update:page', props.currentPage + 1);
    }
};

const handlePerPageChange = (event: Event): void => {
    const target = event.target as HTMLSelectElement;
    const value = Number(target.value);
    console.log('perPage changed', { value, oldValue: props.perPage });
    emit('update:perPage', value);
};
</script>

<style scoped>
.pagination {
    position: sticky;
    bottom: 1rem;
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    padding: 1rem;
    z-index: 100;
    margin-top: 2rem;
    border: 1px solid #dee2e6;
}

.pagination-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.pagination-info {
    flex: 1;
    min-width: 120px;
}

.pagination-text {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.pagination-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    transform: translateY(-2px);
}

.btn-primary:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.btn-icon {
    width: 1rem;
    height: 1rem;
}

.pagination-per-page {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0;
}

.form-select {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
    font-size: 0.875rem;
    cursor: pointer;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;

    &:hover {
        border-color: #adb5bd;
    }

    &:focus {
        outline: 0;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
}

@media (max-width: 640px) {
    .pagination-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .pagination-info {
        text-align: center;
    }

    .pagination-buttons {
        justify-content: center;
    }

    .pagination-per-page {
        justify-content: center;
    }
}
</style>
