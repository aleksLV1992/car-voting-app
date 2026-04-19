import { defineStore } from 'pinia';
import axios from 'axios';
import type { ModelRecord } from '@/types';

interface ModelState {
    models: ModelRecord[];
    loading: boolean;
    error: string | null;
}

export const useModelStore = defineStore('model', {
    state: (): ModelState => ({
        models: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchModels(make?: string): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                const url = make ? `/api/models/${make}` : '/api/models';
                const response = await axios.get(url);

                if (response.data.success) {
                    this.models = response.data.data;
                }
            } catch (error) {
                this.error = 'Ошибка при загрузке моделей';
                console.error('Failed to fetch models:', error);
            } finally {
                this.loading = false;
            }
        },

        async fetchModelsByMake(make: string): Promise<void> {
            await this.fetchModels(make);
        },
    },
});
