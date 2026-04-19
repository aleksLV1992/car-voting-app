<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель автомобиля
 *
 * @property int $id
 * @property string $image Путь к изображению относительно storage/app/public
 * @property string $make Марка автомобиля (например: BMW, Mercedes)
 * @property string $model Модель автомобиля (например: X5, C-Class)
 * @property int $year Год выпуска
 * @property int $odometer Пробег в милях
 * @property string $units Единицы измерения (US, Metric)
 * @property string $engine Тип двигателя (например: 2.0L I4)
 * @property string $transmission Тип КПП (Automatic, Manual)
 * @property string $color Цвет автомобиля
 * @property string|null $brand Бренд (устаревшее поле)
 * @property int|null $winning_bid_amount Сумма выигрышной ставки
 * @property string|null $winning_bid_location Локация выигрышной ставки
 * @property int $votes_count Количество голосов
 * @property \Carbon\Carbon $created_at Дата создания
 * @property \Carbon\Carbon $updated_at Дата обновления
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Vote> $votes Коллекция голосов
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Car whereMake(string $make)
 * @method static \Illuminate\Database\Eloquent\Builder|Car whereModel(string $model)
 * @method static \Illuminate\Database\Eloquent\Builder|Car whereYear(int $year)
 * @method static \Illuminate\Database\Eloquent\Builder|Car whereVotesCount(int $count)
 * @method static \Illuminate\Database\Eloquent\Builder|Car withVotes()
 */
class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'make',
        'model',
        'year',
        'odometer',
        'units',
        'engine',
        'transmission',
        'color',
        'brand',
        'winning_bid_amount',
        'winning_bid_location',
        'votes_count',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'integer',
        'winning_bid_amount' => 'integer',
        'votes_count' => 'integer',
    ];

    /**
     * Получить все голоса для этого автомобиля
     *
     * @return HasMany<Vote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Увеличить счётчик голосов на 1
     *
     * @return void
     */
    public function incrementVotes(): void
    {
        $this->increment('votes_count');
    }
}
