<template>
    <div class="statistics-page">
        <div class="container">
            <h1 class="page-title">Статистика голосования</h1>

            <!-- Фильтры -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="brandFilter" class="filter-label">Бренд:</label>
                        <select
                            id="brandFilter"
                            v-model="selectedModel"
                            @change="applyFilters"
                            class="filter-input"
                        >
                            <option value="">Все бренды</option>
                            <option v-for="brand in models" :key="brand" :value="brand">
                                {{ brand }}
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="yearFrom" class="filter-label">Год от:</label>
                        <input
                            type="number"
                            id="yearFrom"
                            v-model.number="yearFrom"
                            @change="applyFilters"
                            :max="currentYear"
                            :min="1900"
                            class="filter-input"
                            :placeholder="currentYear - 25"
                        />
                    </div>

                    <div class="filter-group">
                        <label for="yearTo" class="filter-label">Год до:</label>
                        <input
                            type="number"
                            id="yearTo"
                            v-model.number="yearTo"
                            @change="applyFilters"
                            :max="currentYear"
                            :min="1900"
                            class="filter-input"
                            :placeholder="currentYear"
                        />
                    </div>

                    <div class="filter-group filter-actions">
                        <button @click="clearFilters" class="clear-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Сбросить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Общая сумма голосов -->
            <div class="total-votes">
                <span class="total-label">Всего голосов:</span>
                <span class="total-value">{{ totalVotes.toLocaleString() }}</span>
            </div>

            <!-- Переключатель вида -->
            <div class="view-switcher">
                <button
                    @click="viewMode = 'cards'"
                    :class="['view-btn', { active: viewMode === 'cards' }]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Карточки
                </button>
                <button
                    @click="viewMode = 'table'"
                    :class="['view-btn', { active: viewMode === 'table' }]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Таблица
                </button>
            </div>

            <!-- Загрузка -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Загрузка статистики...</p>
            </div>

            <!-- Нет данных -->
            <div v-else-if="!loading && cars.length === 0" class="no-data-state">
                <svg class="no-data-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="no-data-title">Нет данных</h3>
                <p class="no-data-text">По выбранным фильтрам не найдено ни одного автомобиля</p>
                <button @click="clearFilters" class="clear-filters-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Сбросить фильтры
                </button>
            </div>

            <!-- Режим карточек -->
            <div v-else-if="viewMode === 'cards'" class="cards-grid">
                <CarCard
                    v-for="car in cars"
                    :key="car.id"
                    :car="car"
                    :show-votes="true"
                />
            </div>

            <!-- Режим таблицы -->
            <TableView
                v-else
                :cars="cars"
                @open-image="openImageViewer"
            />

            <!-- ImageViewer для таблиц -->
            <ImageViewer
                :car="selectedCar"
                :is-open="isImageViewerOpen"
                @close="closeImageViewer"
            />

            <!-- Пагинация -->
            <Pagination
                v-if="!loading && cars.length > 0"
                :current-page="currentPage"
                :last-page="lastPage"
                :per-page="perPage"
                :per-page-options="viewMode === 'cards' ? [9, 18, 27, 36] : [10, 25, 50, 100]"
                @update:page="handlePageChange"
                @update:per-page="updatePerPage"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useStatisticsStore } from '@/stores/statisticsStore';
import CarCard from '@/components/CarCard.vue';
import TableView from '@/components/TableView.vue';
import Pagination from '@/components/Pagination.vue';
import ImageViewer from '@/components/ImageViewer.vue';
import type { CarRecord } from '@/types';

const statisticsStore = useStatisticsStore();
const { cars, loading, models, totalVotes, currentPage, lastPage, perPage } = storeToRefs(statisticsStore);

const viewMode = ref<'cards' | 'table'>('cards');
const currentYear = new Date().getFullYear();
const selectedModel = ref('');
const yearFrom = ref<number | null>(null);
const yearTo = ref<number | null>(null);
const selectedCar = ref<CarRecord | null>(null);
const isImageViewerOpen = ref(false);

// Сохранение режима просмотра
watch(viewMode, (newValue) => {
    localStorage.setItem('statisticsViewMode', newValue);
});

// Валидация годов
const validateYears = (): boolean => {
    if (yearFrom.value && yearFrom.value > currentYear) {
        alert(`Год "от" не может быть больше текущего года (${currentYear})`);
        yearFrom.value = currentYear;
        return false;
    }
    
    if (yearTo.value && yearTo.value > currentYear) {
        alert(`Год "до" не может быть больше текущего года (${currentYear})`);
        yearTo.value = currentYear;
        return false;
    }
    
    if (yearFrom.value && yearTo.value && yearFrom.value > yearTo.value) {
        alert('Год "от" не может быть больше года "до"');
        [yearFrom.value, yearTo.value] = [yearTo.value, yearFrom.value];
    }
    
    return true;
};

const applyFilters = (): void => {
    if (!validateYears()) {
        return;
    }
    
    statisticsStore.setFilters({
        model: selectedModel.value || null,
        yearFrom: yearFrom.value,
        yearTo: yearTo.value,
    });
    statisticsStore.fetchStatistics(1, perPage.value);
};

const clearFilters = (): void => {
    selectedModel.value = '';
    yearFrom.value = null;
    yearTo.value = null;
    statisticsStore.clearFilters();
    statisticsStore.fetchStatistics(1, perPage.value);
};

const handlePageChange = (newPage: number): void => {
    currentPage.value = newPage;
    statisticsStore.fetchStatistics(newPage, perPage.value);
};

const updatePerPage = (newPerPage: number): void => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    statisticsStore.fetchStatistics(currentPage.value, perPage.value);
};

const openImageViewer = (car: CarRecord): void => {
    selectedCar.value = car;
    isImageViewerOpen.value = true;
};

const closeImageViewer = (): void => {
    selectedCar.value = null;
    isImageViewerOpen.value = false;
};

onMounted(() => {
    statisticsStore.fetchModels();
    statisticsStore.fetchStatistics(1, 9);
});
</script>

<style scoped>
.statistics-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 2rem 1rem;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.page-title {
    text-align: center;
    color: #1f2937;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
}

.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.filter-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
}

.filter-actions {
    justify-content: flex-end;
}

.clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-btn:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.total-votes {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    border-radius: 0.75rem;
    text-align: center;
    margin-bottom: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.total-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.125rem;
    margin-right: 1rem;
}

.total-value {
    color: white;
    font-size: 2rem;
    font-weight: 700;
}

.view-switcher {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.view-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.loading-state {
    background: white;
    padding: 3rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.no-data-state {
    background: white;
    padding: 4rem 2rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.no-data-icon {
    width: 5rem;
    height: 5rem;
    color: #9ca3af;
    margin: 0 auto 1.5rem;
}

.no-data-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.no-data-text {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 2rem;
}

.clear-filters-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #0d6efd;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-filters-btn:hover {
    background: #0b5ed7;
    transform: translateY(-2px);
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.table-container {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
}

.stats-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stats-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.2s ease;
}

.stats-table tbody tr:hover {
    background-color: #f9fafb;
}

.stats-table td {
    padding: 1rem;
    vertical-align: middle;
}

.car-thumb {
    width: 4rem;
    height: 3rem;
    object-fit: cover;
    border-radius: 0.375rem;
}

.car-name {
    font-weight: 600;
    color: #1f2937;
}

.price-cell {
    font-weight: 700;
    color: #10b981;
}

.votes-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    padding: 0.25rem 0.75rem;
    background: #d1fae5;
    color: #065f46;
    border-radius: 9999px;
    font-weight: 700;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .page-title {
        font-size: 1.75rem;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .cards-grid {
        grid-template-columns: 1fr;
    }

    .total-label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .total-value {
        font-size: 1.5rem;
    }
}
</style>
