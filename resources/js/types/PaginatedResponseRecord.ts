/**
 * Record для ответа API с пагинацией
 */
export type PaginatedResponseRecord<T> = {
    data: T[];
    total: number;
    last_page: number;
    current_page: number;
    per_page: number;
    total_votes?: number;
};
