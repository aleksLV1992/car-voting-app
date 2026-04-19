import { defineStore } from 'pinia';
import axios from 'axios';
import type { CarRecord, StatisticsStateRecord, StatisticsFiltersRecord } from '@/types';

export const useStatisticsStore = defineStore('statistics', {
    state: (): StatisticsStateRecord => ({
        cars: [],
        loading: false,
        error: null,
        models: [],
        totalVotes: 0,
        currentPage: 1,
        lastPage: 1,
        perPage: 9,
        filters: {
            model: null,
            yearFrom: null,
            yearTo: null,
        },
    }),

    actions: {
        async fetchModels(): Promise<void> {
            try {
                const response = await axios.get('/api/cars/models');
                this.models = response.data.data || [];
            } catch (error) {
                console.error('Failed to fetch models:', error);
            }
        },

        async fetchStatistics(page: number = 1, perPage: number = 9): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                const params: Record<string, string | number | null> = {
                    page,
                    perPage,
                    model: this.filters.model || null,
                    yearFrom: this.filters.yearFrom || null,
                    yearTo: this.filters.yearTo || null,
                };

                const response = await axios.get('/api/cars/statistics', { params });
                this.cars = response.data.data as CarRecord[];
                this.totalVotes = response.data.total_votes || 0;
                this.currentPage = response.data.current_page || 1;
                this.lastPage = response.data.last_page || 1;
                this.perPage = response.data.per_page || perPage;
            } catch (error) {
                this.error = 'Ошибка при загрузке статистики';
                console.error('Failed to fetch statistics:', error);
            } finally {
                this.loading = false;
            }
        },

        setFilters(filters: Partial<StatisticsFiltersRecord>): void {
            this.filters = { ...this.filters, ...filters };
        },

        clearFilters(): void {
            this.filters = {
                model: null,
                yearFrom: null,
                yearTo: null,
            };
        },
    },
});
