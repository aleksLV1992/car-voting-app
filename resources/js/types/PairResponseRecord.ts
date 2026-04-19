import type { CarPairRecord } from './CarPairRecord';

/**
 * Record для ответа API пары автомобилей
 */
export type PairResponseRecord = {
    success: boolean;
    data: CarPairRecord | null;
    message?: string;
};
