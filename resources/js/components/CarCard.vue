<template>
    <div class="car-card" :class="{ 'with-actions': showActions }">
        <div class="car-image-container" @click="openImageViewer">
            <img 
                :src="imageUrl" 
                :alt="carTitle" 
                class="car-image" 
                @error="handleImageError"
                v-show="!imageError"
            />
            <div v-if="imageError" class="car-image-error">
                <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p>Фото не найдено</p>
            </div>
            <div v-if="!imageError" class="car-overlay">
                <div class="car-info-overlay">
                    <h3 class="car-title-overlay">{{ carTitle }}</h3>
                </div>
            </div>
        </div>

        <div class="car-details">
            <h3 class="car-title">{{ carTitle }}</h3>
            <p class="car-year">{{ car.year }} год</p>
            <div class="car-specs">
                <div class="spec-item">
                    <span class="spec-label">Пробег:</span>
                    <span class="spec-value">{{ typeof car.odometer === 'number' ? car.odometer.toLocaleString() : '0' }} миль</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Двигатель:</span>
                    <span class="spec-value">{{ car.engine || 'N/A' }}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">КПП:</span>
                    <span class="spec-value">{{ car.transmission || 'N/A' }}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Цвет:</span>
                    <span class="spec-value">{{ car.color || 'N/A' }}</span>
                </div>
            </div>
            <p class="car-price">${{ typeof car.winning_bid_amount === 'number' ? car.winning_bid_amount.toLocaleString() : '0' }}</p>
        </div>

        <div v-if="showVote" class="car-vote-section">
            <button @click="handleVote" class="btn-vote">
                <svg class="btn-vote-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                </svg>
            </button>
        </div>

        <div v-if="showVotes && car.votes_count !== undefined" class="car-votes-count">
            <span class="votes-badge">{{ car.votes_count }}</span>
            <span class="votes-label">голосов</span>
        </div>

        <div v-if="showActions" class="car-actions">
            <slot name="actions"></slot>
        </div>

        <ImageViewer :car="car" :is-open="isImageViewerOpen" @close="closeImageViewer" />
    </div>
</template>

<script setup lang="ts">
import type { CarRecord } from '@/types';
import { computed, ref } from 'vue';
import ImageViewer from './ImageViewer.vue';

interface Props {
    car: CarRecord;
    showVote?: boolean;
    showVotes?: boolean;
    showActions?: boolean;
}

interface Emits {
    (e: 'vote', carId: number): void;
}

const props = withDefaults(defineProps<Props>(), {
    showVote: false,
    showVotes: false,
    showActions: false,
});

const emit = defineEmits<Emits>();

const imageError = ref(false);
const isImageViewerOpen = ref(false);

const carTitle = computed(() => `${props.car.make} ${props.car.model}`);
const imageUrl = computed(() => `/storage/${props.car.image}`);

const handleImageError = (): void => {
    imageError.value = true;
};

const handleVote = (): void => {
    emit('vote', props.car.id);
};

const openImageViewer = (): void => {
    isImageViewerOpen.value = true;
};

const closeImageViewer = (): void => {
    isImageViewerOpen.value = false;
};
</script>

<style scoped>
.car-card {
    position: relative;
    border-radius: 0.375rem;
    overflow: hidden;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    background: #fff;
    border: 1px solid #dee2e6;

    &:hover {
        transform: translateY(-0.125rem);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
}

.car-image-container {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: #f8f9fa;
    cursor: pointer;
}

.car-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.car-card:hover .car-image {
    transform: scale(1.08);
}

.car-image-error {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #6c757d;
    gap: 0.5rem;

    .error-icon {
        width: 4rem;
        height: 4rem;
    }

    p {
        font-size: 0.875rem;
        font-weight: 500;
    }
}

.car-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.15s ease-in-out;
}

.car-card:hover .car-overlay {
    opacity: 1;
}

.car-info-overlay {
    color: #fff;
    text-align: center;
    padding: 1rem;
}

.car-title-overlay {
    font-size: 1.5rem;
    font-weight: 700;
    text-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.3);
}

.car-details {
    padding: 1.25rem;
}

.car-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.5rem;
}

.car-year {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.75rem;
}

.car-specs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
}

.spec-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.spec-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.spec-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #212529;
}

.car-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #198754;
}

.car-vote-section {
    padding: 1rem;
    display: flex;
    justify-content: center;
}

.btn-vote {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);

    &:hover {
        background-color: #0b5ed7;
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    &:active {
        transform: translateY(0);
    }
}

.btn-vote-icon {
    width: 1.5rem;
    height: 1.5rem;
}

.car-votes-count {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255, 255, 255, 0.95);
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #dee2e6;
}

.votes-badge {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: #198754;
}

.votes-label {
    font-size: 0.625rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.car-actions {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

.with-actions {
    margin-bottom: 1rem;
}
</style>
