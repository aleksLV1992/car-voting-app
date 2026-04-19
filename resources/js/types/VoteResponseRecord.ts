/**
 * Record для ответа API голосования
 */
export type VoteResponseRecord = {
    success: boolean;
    message: string;
    votes_count: number;
};
