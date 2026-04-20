<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель голоса
 *
 * @property int $id
 * @property int $car_id ID автомобиля
 * @property string $voter_id Идентификатор голосующего (IP адрес)
 * @property Carbon $created_at Дата создания
 * @property Carbon $updated_at Дата обновления
 * @property-read Car $car Автомобиль, за который проголосовали
 *
 * @method static Builder|Vote whereCarId(int $car_id)
 * @method static Builder|Vote whereVoterId(string $voter_id)
 * @method static Builder|Vote uniqueVote(int $car_id, string $voter_id)
 */
class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'voter_id',
    ];

    /**
     * Получить автомобиль для этого голоса
     *
     * @return BelongsTo<Car>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
