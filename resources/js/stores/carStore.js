import { defineStore } from 'pinia';
import axios from 'axios';

export const useCarStore = defineStore('car', {
    state: () => ({
        models: [],
        currentPair: null,
        selectedModel: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchModels() {
            this.loading = true;
            try {
                const response = await axios.get('/api/cars/models');
                this.models = response.data.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async fetchPair(model = null) {
            this.loading = true;
            try {
                const url = model ? `/api/voting/pair?model=${model}` : '/api/voting/pair';
                const response = await axios.get(url);
                this.currentPair = response.data.data;
                this.error = null;
            } catch (error) {
                this.error = error.response?.data?.message || 'Ошибка загрузки пары';
                this.currentPair = null;
            } finally {
                this.loading = false;
            }
        },

        async vote(carId) {
            try {
                return await axios.post(`/api/voting/${carId}`);
            } catch (error) {
                throw error;
            }
        },

        setSelectedModel(model) {
            this.selectedModel = model;
        },

        clearError() {
            this.error = null;
        },
    },
});
