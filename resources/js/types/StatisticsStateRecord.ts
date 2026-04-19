import type { CarRecord } from './CarRecord';
import type { StatisticsFiltersRecord } from './StatisticsFiltersRecord';

/**
 * Record для состояния хранилища статистики
 */
export type StatisticsStateRecord = {
    cars: CarRecord[];
    loading: boolean;
    error: string | null;
    models: string[];
    totalVotes: number;
    currentPage: number;
    lastPage: number;
    perPage: number;
    filters: StatisticsFiltersRecord;
};
