/**
 * Record для модели автомобиля
 * Используется для строгой типизации данных модели
 */
export type CarRecord = {
    id: number;
    image: string;
    make: string;
    model: string;
    year: number;
    odometer: number;
    units: string;
    engine: string;
    transmission: string;
    color: string;
    brand: string | null;
    winning_bid_amount: number | null;
    winning_bid_location: string | null;
    created_at: string;
    updated_at: string;
    votes_count: number;
};
