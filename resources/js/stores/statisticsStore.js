import { defineStore } from 'pinia';
import axios from 'axios';

export const useStatisticsStore = defineStore('statistics', {
    state: () => ({
        cars: [],
        totalVotes: 0,
        models: [],
        selectedModel: null,
        yearFrom: null,
        yearTo: null,
        loading: false,
        error: null,
        currentPage: 1,
        perPage: 9,
        total: 0,
        lastPage: 1,
    }),

    actions: {
        async fetchModels() {
            try {
                const response = await axios.get('/api/cars/models');
                this.models = response.data.data;
            } catch (error) {
                this.error = error.message;
            }
        },

        async fetchStatistics() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.selectedModel) params.append('model', this.selectedModel);
                if (this.yearFrom) params.append('yearFrom', this.yearFrom);
                if (this.yearTo) params.append('yearTo', this.yearTo);
                params.append('page', this.currentPage);
                params.append('per_page', this.perPage);

                const response = await axios.get(`/api/cars/statistics?${params}`);
                this.cars = response.data.data;
                this.totalVotes = response.data.total_votes;
                this.total = response.data.total || 0;
                this.lastPage = response.data.last_page || 1;
                this.error = null;
            } catch (error) {
                this.error = error.response?.data?.message || 'Ошибка загрузки статистики';
            } finally {
                this.loading = false;
            }
        },

        setPage(page) {
            if (page >= 1 && page <= this.lastPage) {
                this.currentPage = page;
                this.fetchStatistics();
            }
        },

        setPerPage(perPage) {
            this.perPage = perPage;
            this.currentPage = 1;
            this.fetchStatistics();
        },

        setFilters({ model, yearFrom, yearTo }) {
            this.selectedModel = model;
            this.yearFrom = yearFrom;
            this.yearTo = yearTo;
            this.currentPage = 1;
        },

        clearFilters() {
            this.selectedModel = null;
            this.yearFrom = null;
            this.yearTo = null;
            this.currentPage = 1;
        },

        clearError() {
            this.error = null;
        },
    },
});
