/**
 * Record для фильтров статистики
 * model - это бренд автомобиля (make), например: "BMW", "CHEVROLET", "FORD"
 */
export type StatisticsFiltersRecord = {
    model?: string | null;  // бренд (make)
    yearFrom?: number | null;
    yearTo?: number | null;
    page?: number;
    perPage?: number;
};
