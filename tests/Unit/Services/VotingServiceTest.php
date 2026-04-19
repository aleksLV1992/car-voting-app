<?php

namespace Tests\Unit\Services;

use App\Models\Car;
use App\Models\Vote;
use App\Repositories\CarRepository;
use App\Repositories\VoteRepository;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VotingServiceTest extends TestCase
{
    use RefreshDatabase;

    private VotingService $votingService;
    private CarRepository $carRepository;
    private VoteRepository $voteRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carRepository = new CarRepository(new Car());
        $this->voteRepository = new VoteRepository(new Vote());
        $this->votingService = new VotingService($this->carRepository, $this->voteRepository);

        Cache::flush();
    }

    public function test_get_random_pair_returns_two_cars(): void
    {
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        Car::factory()->create(['make' => 'Audi', 'model' => 'A4']);

        $pair = $this->votingService->getRandomPair();

        $this->assertNotNull($pair->left);
        $this->assertNotNull($pair->right);
        $this->assertNotEquals($pair->left->id, $pair->right->id);
    }

    public function test_get_random_pair_by_make_returns_only_cars_of_that_make(): void
    {
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        Car::factory()->create(['make' => 'Audi', 'model' => 'A4']);

        $pair = $this->votingService->getRandomPair('BMW');

        $this->assertNotNull($pair->left);
        $this->assertNotNull($pair->right);
        $this->assertEquals('BMW', $pair->left->make);
        $this->assertEquals('BMW', $pair->right->make);
    }

    public function test_get_random_pair_returns_null_when_not_enough_cars(): void
    {
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);

        $pair = $this->votingService->getRandomPair('BMW');

        $this->assertNull($pair->left);
        $this->assertNull($pair->right);
    }

    public function test_get_random_pair_excludes_already_shown_cars(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        $car2 = Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        $car3 = Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);

        $voterId = '127.0.0.1';

        // Получаем первую пару
        $pair1 = $this->votingService->getRandomPair('BMW', $voterId);
        $this->assertNotNull($pair1->left);
        $this->assertNotNull($pair1->right);

        // Получаем вторую пару - должна быть другая
        $pair2 = $this->votingService->getRandomPair('BMW', $voterId);
        $this->assertNotNull($pair2->left);
        $this->assertNotNull($pair2->right);

        // Проверяем, что пары разные
        $pair1Ids = [$pair1->left->id, $pair1->right->id];
        $pair2Ids = [$pair2->left->id, $pair2->right->id];

        $this->assertNotEquals($pair1Ids, $pair2Ids);
    }

    public function test_get_random_pair_resets_cache_when_all_cars_shown(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        $car2 = Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);

        $voterId = '127.0.0.1';

        // Показываем все автомобили
        $this->votingService->getRandomPair('BMW', $voterId);

        // Следующий запрос должен сбросить кэш и вернуть те же автомобили
        $pair = $this->votingService->getRandomPair('BMW', $voterId);
        $this->assertNotNull($pair->left);
        $this->assertNotNull($pair->right);
    }

    public function test_vote_for_car_increments_votes_count(): void
    {
        $car = Car::factory()->create(['votes_count' => 0]);

        $this->votingService->voteForCar($car->id, '127.0.0.1');

        $car->refresh();
        $this->assertEquals(1, $car->votes_count);
    }

    public function test_vote_for_car_creates_vote_record(): void
    {
        $car = Car::factory()->create();
        $voterId = '127.0.0.1';

        $this->votingService->voteForCar($car->id, $voterId);

        $this->assertDatabaseHas('votes', [
            'car_id' => $car->id,
            'voter_id' => $voterId,
        ]);
    }

    public function test_vote_for_car_prevents_duplicate_votes(): void
    {
        $car = Car::factory()->create(['votes_count' => 0]);
        $voterId = '127.0.0.1';

        // Первое голосование
        $result1 = $this->votingService->voteForCar($car->id, $voterId);
        $this->assertTrue($result1);

        // Повторное голосование
        $result2 = $this->votingService->voteForCar($car->id, $voterId);
        $this->assertFalse($result2);

        // Счётчик должен быть 1
        $car->refresh();
        $this->assertEquals(1, $car->votes_count);
    }

    public function test_has_voted_returns_true_if_already_voted(): void
    {
        $car = Car::factory()->create();
        $voterId = '127.0.0.1';

        Vote::create([
            'car_id' => $car->id,
            'voter_id' => $voterId,
        ]);

        $this->assertTrue($this->votingService->hasVoted($car->id, $voterId));
    }

    public function test_has_voted_returns_false_if_not_voted(): void
    {
        $car = Car::factory()->create();
        $voterId = '127.0.0.1';

        $this->assertFalse($this->votingService->hasVoted($car->id, $voterId));
    }

    public function test_get_votes_count_returns_correct_count(): void
    {
        $car = Car::factory()->create();

        Vote::factory()->count(5)->create(['car_id' => $car->id]);

        $count = $this->votingService->getVotesCount($car->id);

        $this->assertEquals(5, $count);
    }

    public function test_has_enough_cars_returns_true_when_enough(): void
    {
        Car::factory()->count(3)->create(['make' => 'BMW']);

        $this->assertTrue($this->votingService->hasEnoughCars('BMW'));
    }

    public function test_has_enough_cars_returns_false_when_not_enough(): void
    {
        Car::factory()->count(1)->create(['make' => 'BMW']);

        $this->assertFalse($this->votingService->hasEnoughCars('BMW'));
    }
}
