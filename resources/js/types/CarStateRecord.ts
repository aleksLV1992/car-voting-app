import type { CarPairRecord } from './CarPairRecord';

/**
 * Record для состояния хранилища автомобилей
 */
export type CarStateRecord = {
    currentPair: CarPairRecord | null;
    loading: boolean;
    error: string | null;
    noMorePairs: boolean;
    insufficientCars: boolean;
    models: string[];
    selectedModel: string | null;
};
