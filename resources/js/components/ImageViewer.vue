<template>
    <div class="image-viewer" v-if="isOpen" @click="close">
        <div class="image-viewer-content" @click.stop>
            <button class="image-viewer-close" @click="close">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="image-viewer-image" v-if="car.image">
                <img :src="imageUrl" :alt="carTitle" @error="handleImageError" />
                <div class="image-viewer-placeholder" v-if="imageError">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Фото не найдено</span>
                </div>
            </div>

            <div class="image-viewer-info">
                <h2 class="image-viewer-title">{{ carTitle }}</h2>
                <div class="image-viewer-grid">
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">Год</span>
                        <span class="image-viewer-value">{{ car.year || '—' }}</span>
                    </div>
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">Пробег</span>
                        <span class="image-viewer-value">{{ typeof car.odometer === 'number' ? car.odometer.toLocaleString() : '0' }} миль</span>
                    </div>
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">Двигатель</span>
                        <span class="image-viewer-value">{{ car.engine || '—' }}</span>
                    </div>
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">КПП</span>
                        <span class="image-viewer-value">{{ car.transmission || '—' }}</span>
                    </div>
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">Цвет</span>
                        <span class="image-viewer-value">{{ car.color || '—' }}</span>
                    </div>
                    <div class="image-viewer-item">
                        <span class="image-viewer-label">Цена</span>
                        <span class="image-viewer-value price">${{ typeof car.winning_bid_amount === 'number' ? car.winning_bid_amount.toLocaleString() : '0' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { CarRecord } from '@/types';

interface Props {
    isOpen: boolean;
    car: CarRecord;
}

interface Emits {
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const imageError = ref(false);

const carTitle = computed(() => {
    return `${props.car.make} ${props.car.model}`;
});

const imageUrl = computed(() => {
    return `/storage/${props.car.image}`;
});

const handleImageError = (): void => {
    imageError.value = true;
};

const close = (): void => {
    imageError.value = false;
    emit('close');
};

watch(
    () => props.isOpen,
    (newValue) => {
        if (newValue) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
    { immediate: true }
);
</script>

<style lang="scss" scoped>
.image-viewer {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
    animation: fadeIn 0.3s ease-out;

    &-content {
        background-color: #fff;
        border-radius: 0.5rem;
        max-width: 48rem;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.3);
    }

    &-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.9);
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color 0.2s;
        z-index: 10;

        &:hover {
            background-color: #f8f9fa;
        }

        svg {
            width: 1.5rem;
            height: 1.5rem;
            color: #212529;
        }
    }

    &-image {
        width: 100%;
        height: 32rem;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;

        img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    }

    &-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        color: #6c757d;

        svg {
            width: 4rem;
            height: 4rem;
        }

        span {
            font-size: 1rem;
        }
    }

    &-info {
        padding: 1.5rem;
    }

    &-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #212529;
        margin: 0 0 1.5rem 0;
    }

    &-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;

        @media (max-width: 576px) {
            grid-template-columns: 1fr;
        }
    }

    &-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    &-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 600;
    }

    &-value {
        font-size: 1rem;
        color: #212529;
        font-weight: 500;

        &.price {
            color: #198754;
            font-weight: 700;
            font-size: 1.25rem;
        }
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
