<template>
    <div class="table-container">
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Фото</th>
                    <th>Модель</th>
                    <th>Год</th>
                    <th class="hidden md:table-cell">Пробег</th>
                    <th class="hidden lg:table-cell">Двигатель</th>
                    <th class="hidden lg:table-cell">КПП</th>
                    <th class="hidden md:table-cell">Цвет</th>
                    <th>Цена</th>
                    <th>Голоса</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="car in cars"
                    :key="car.id"
                    class="table-row"
                >
                    <td>
                        <div class="image-wrapper" @click="$emit('openImage', car)">
                            <img
                                :src="getImageUrl(car.image)"
                                :alt="`${car.make} ${car.model}`"
                                class="car-thumb"
                                @error="handleImageError"
                            />
                        </div>
                    </td>
                    <td>
                        <div class="car-name">{{ car.make }} {{ car.model }}</div>
                    </td>
                    <td>{{ car.year }}</td>
                    <td class="hidden md:table-cell">
                        {{ typeof car.odometer === 'number' ? car.odometer.toLocaleString() : '0' }} миль
                    </td>
                    <td class="hidden lg:table-cell">{{ car.engine || '—' }}</td>
                    <td class="hidden lg:table-cell">{{ car.transmission || '—' }}</td>
                    <td class="hidden md:table-cell">{{ car.color || '—' }}</td>
                    <td class="price-cell">
                        ${{ typeof car.winning_bid_amount === 'number' ? car.winning_bid_amount.toLocaleString() : '0' }}
                    </td>
                    <td>
                        <span class="votes-badge">{{ car.votes_count }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
import type { CarRecord } from '@/types';

interface Props {
    cars: CarRecord[];
}

interface Emits {
    (e: 'openImage', car: CarRecord): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const getImageUrl = (image: string): string => {
    return image.startsWith('http') ? image : `/storage/${image}`;
};

const handleImageError = (event: Event): void => {
    const img = event.target as HTMLImageElement;
    img.style.display = 'none';
};
</script>

<style scoped>
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

.image-wrapper {
    cursor: pointer;
    display: inline-block;
}

.image-wrapper:hover img {
    transform: scale(1.08);
}

.car-thumb {
    width: 4rem;
    height: 3rem;
    object-fit: cover;
    border-radius: 0.375rem;
    transition: transform 0.3s ease;
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
</style>
