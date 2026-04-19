<template>
    <div class="voting-page">
        <div class="container">
            <h1 class="page-title">Голосование за автомобили</h1>

            <!-- Фильтры -->
            <div class="filters-container">
                <div class="filter-item">
                    <label for="brandSelect" class="selector-label">Бренд:</label>
                    <select
                        id="brandSelect"
                        v-model="selectedModel"
                        @change="onFilterChange"
                        class="selector-input"
                    >
                        <option value="">Все бренды</option>
                        <option v-for="brand in models" :key="brand" :value="brand">
                            {{ brand }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Загрузка -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Загрузка пары автомобилей...</p>
            </div>

            <!-- Ошибка: нет пар -->
            <div v-else-if="noMorePairs" class="no-pairs-state">
                <svg class="no-pairs-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="no-pairs-title">
                    {{ insufficientCars ? 'Недостаточно автомобилей' : 'Пары закончились!' }}
                </h3>
                <p class="no-pairs-message">
                    <span v-if="insufficientCars">
                        Для бренда "{{ selectedModel }}" недостаточно автомобилей для голосования (менее 2)
                    </span>
                    <span v-else>
                        Вы просмотрели все доступные автомобили{{ selectedModel ? ` бренда "${selectedModel}"` : '' }}.
                    </span>
                </p>
                <button @click="resetFilter" class="reset-filter-button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Сбросить фильтр
                </button>
            </div>

            <!-- Ошибка: другая -->
            <div v-else-if="error" class="error-state">
                <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p>{{ error }}</p>
                <button @click="fetchPair" class="retry-button">Попробовать снова</button>
            </div>

            <!-- Пара автомобилей -->
            <div v-else-if="currentPair" class="cars-container" :key="currentPair.left.id + '-' + currentPair.right.id">
                <div class="car-column">
                    <CarCard
                        :car="currentPair.left"
                        :show-vote="true"
                        @vote="handleVote"
                    />
                </div>

                <div class="vs-divider">
                    <span>VS</span>
                </div>

                <div class="car-column">
                    <CarCard
                        :car="currentPair.right"
                        :show-vote="true"
                        @vote="handleVote"
                    />
                </div>
            </div>

            <!-- Кнопка новой пары -->
            <div v-if="currentPair" class="new-pair-section">
                <button @click="fetchPair" class="new-pair-button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Показать новую пару
                </button>
            </div>
        </div>

        <!-- Alert компонент -->
        <Alert
            v-model="alertVisible"
            :type="alertType"
            :message="alertMessage"
            @close="alertVisible = false"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useCarStore } from '@/stores/carStore';
import CarCard from '@/components/CarCard.vue';
import Alert from '@/components/Alert.vue';
import type { AlertType } from '@/types';

const carStore = useCarStore();
const { currentPair, loading, error, models, selectedModel } = storeToRefs(carStore);

const alertVisible = ref(false);
const alertType = ref<AlertType>('success');
const alertMessage = ref('');

// Вычисляемое свойство: нет ли пар (специфичное сообщение)
const noMorePairs = computed(() => {
    return carStore.noMorePairs === true;
});

const insufficientCars = computed(() => {
    return carStore.insufficientCars === true;
});

const onFilterChange = (): void => {
    const model = selectedModel.value || null;
    carStore.setSelectedModel(model);
    // Передаём модель напрямую в fetchPair
    carStore.fetchPair(model);
};

const resetFilter = (): void => {
    carStore.setSelectedModel(null);
    // Передаём null напрямую в fetchPair
    carStore.fetchPair(null);
};

const handleVote = async (carId: number): Promise<void> => {
    try {
        const response = await carStore.vote(carId);
        alertType.value = 'success';
        alertMessage.value = response.message || 'Голос успешно засчитан!';
        alertVisible.value = true;
        // Автоматическая загрузка новой пары после голосования
        setTimeout(() => {
            carStore.fetchPair();
        }, 1500);
    } catch (err) {
        alertType.value = 'error';
        alertMessage.value = err instanceof Error ? err.message : 'Ошибка при голосовании';
        alertVisible.value = true;
    }
};

const fetchPair = (): void => {
    carStore.fetchPair();
};

onMounted(() => {
    carStore.fetchModels();
    carStore.fetchPair();
});
</script>

<style scoped>
.voting-page {
    min-height: 100vh;
    background-color: #f3f4f6;
    padding: 2rem 1rem;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.no-pairs-state {
    background: white;
    padding: 3rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.no-pairs-icon {
    width: 4rem;
    height: 4rem;
    color: #f59e0b;
    margin: 0 auto 1rem;
}

.no-pairs-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.no-pairs-message {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.reset-filter-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.reset-filter-button:hover:not(:disabled) {
    background-color: #0b5ed7;
    transform: translateY(-2px);
}

.reset-filter-button:disabled {
    background-color: #6c757d;
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.page-title {
    text-align: center;
    color: #1f2937;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
}

.filters-container {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.selector-label {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.selector-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.selector-input:focus {
    outline: none;
    border-color: #667eea;
}

.loading-state,
.error-state {
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

.error-icon {
    width: 4rem;
    height: 4rem;
    color: #ef4444;
    margin: 0 auto 1rem;
}

.retry-button {
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.retry-button:hover:not(:disabled) {
    background-color: #0b5ed7;
    transform: translateY(-2px);
}

.retry-button:disabled {
    background-color: #6c757d;
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.cars-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.car-column {
    flex: 1;
    max-width: 500px;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.vs-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: #e5e7eb;
    border-radius: 50%;
    font-size: 1.5rem;
    font-weight: 700;
    color: #6b7280;
    flex-shrink: 0;
}

.new-pair-section {
    text-align: center;
}

.new-pair-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 1.125rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.new-pair-button:hover:not(:disabled) {
    background-color: #0b5ed7;
    transform: translateY(-2px);
}

.new-pair-button:disabled {
    background-color: #6c757d;
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 1024px) {
    .cars-container {
        flex-direction: column;
        gap: 1.5rem;
    }

    .vs-divider {
        width: 60px;
        height: 60px;
        font-size: 1.25rem;
    }
}

@media (max-width: 640px) {
    .page-title {
        font-size: 1.75rem;
    }

    .filters-container {
        padding: 1rem;
    }
}
</style>
