import { defineStore } from 'pinia';
import axios from 'axios';
import type { CarPairRecord, VoteResponseRecord, CarStateRecord } from '@/types';

export const useCarStore = defineStore('car', {
    state: (): CarStateRecord => ({
        currentPair: null,
        loading: false,
        error: null,
        noMorePairs: false,
        insufficientCars: false,
        models: [],
        selectedModel: null,
    }),

    actions: {
        async fetchModels(): Promise<void> {
            try {
                // Пробуем новый API /api/models
                let response;
                try {
                    response = await axios.get('/api/models');
                    if (response.data.success && response.data.data) {
                        const makes = new Set<string>();
                        response.data.data.forEach((item: { make: string }) => {
                            makes.add(item.make);
                        });
                        this.models = Array.from(makes).sort();
                        return;
                    }
                } catch {
                    // Если новый API не работает, пробуем старый
                }

                // Фолбэк на старый API /api/cars/models
                response = await axios.get('/api/cars/models');
                if (response.data.success && response.data.data) {
                    this.models = response.data.data.sort();
                }
            } catch (error) {
                console.error('Failed to fetch models:', error);
            }
        },

        async fetchPair(model: string | null = null): Promise<void> {
            this.loading = true;
            this.error = null;
            this.noMorePairs = false;
            this.insufficientCars = false;

            // Если модель не передана, используем из state
            const selectedModel = model ?? this.selectedModel;

            try {
                const params: Record<string, string | null> = {};
                if (selectedModel) {
                    params.model = selectedModel;
                }

                const response = await axios.get('/api/voting/pair', { params });

                if (response.data.success && response.data.data) {
                    this.currentPair = response.data.data;
                } else {
                    this.currentPair = null;
                    this.noMorePairs = true;
                    this.insufficientCars = response.data.insufficientCars || false;
                    this.error = null;
                }
            } catch (error) {
                this.error = 'Ошибка при загрузке пары автомобилей';
                this.currentPair = null;
                this.noMorePairs = false;
                this.insufficientCars = false;
                console.error('Failed to fetch pair:', error);
            } finally {
                this.loading = false;
            }
        },

        async vote(carId: number): Promise<VoteResponseRecord> {
            try {
                const response = await axios.post(`/api/voting/${carId}`);
                await this.fetchPair();
                return response.data;
            } catch (error) {
                if (axios.isAxiosError(error) && error.response?.status === 409) {
                    throw new Error(error.response.data.message || 'Вы уже голосовали за этот автомобиль');
                }
                throw new Error('Ошибка при голосовании');
            }
        },

        setSelectedModel(model: string | null): void {
            this.selectedModel = model;
        },

        clearError(): void {
            this.error = null;
        },
    },
});
