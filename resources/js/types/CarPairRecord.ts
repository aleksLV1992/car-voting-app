import type { CarRecord } from './CarRecord';

/**
 * Record для пары автомобилей
 */
export type CarPairRecord = {
    left: CarRecord;
    right: CarRecord;
};
