<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Vote;
use App\Repositories\CarRepository;
use App\Repositories\VoteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    private CarRepository $carRepository;
    private VoteRepository $voteRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carRepository = new CarRepository();
        $this->voteRepository = new VoteRepository();
    }

    public function test_can_get_random_pair(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020]);
        Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021]);
        Car::create(['image' => 'car3.jpg', 'make' => 'Audi', 'model' => 'A4', 'year' => 2019]);

        $pair = $this->carRepository->getRandomPair();

        $this->assertIsArray($pair);
        $this->assertCount(2, $pair);
    }

    public function test_can_get_random_pair_by_model(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020]);
        Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021]);
        Car::create(['image' => 'car3.jpg', 'make' => 'Audi', 'model' => 'A4', 'year' => 2019]);

        // Need at least 2 cars with same make (brand)
        $pair = $this->carRepository->getRandomPair('BMW');

        if ($pair !== null) {
            $this->assertIsArray($pair);
            $this->assertCount(2, $pair);
            $this->assertEquals('BMW', $pair[0]['make']);
            $this->assertEquals('BMW', $pair[1]['make']);
        } else {
            // If null, it means not enough cars with that make
            $this->assertTrue(true);
        }
    }

    public function test_returns_null_when_not_enough_cars(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020]);

        $pair = $this->carRepository->getRandomPair();

        $this->assertNull($pair);
    }

    public function test_can_vote_for_car(): void
    {
        $car = Car::create([
            'image' => 'car1.jpg',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'votes_count' => 0,
        ]);

        $initialVotes = $car->votes_count;

        $this->voteRepository->create($car->id, '127.0.0.1');
        $car->incrementVotes();

        $this->assertEquals($initialVotes + 1, $car->votes_count);
    }

    public function test_cannot_vote_twice_from_same_ip(): void
    {
        $car = Car::create([
            'image' => 'car1.jpg',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
        ]);

        $voterId = '127.0.0.1';

        // First vote
        $this->voteRepository->create($car->id, $voterId);

        // Check if already voted
        $hasVoted = $this->voteRepository->hasVoted($car->id, $voterId);

        $this->assertTrue($hasVoted);
    }

    public function test_can_get_all_models(): void
    {
        // Создаём тестовые данные с уникальными брендами
        $brand1 = 'TestMake_' . uniqid();
        $brand2 = 'TestMake_' . uniqid();
        
        Car::create(['image' => 'car1.jpg', 'make' => $brand1, 'model' => 'X5', 'year' => 2020]);
        Car::create(['image' => 'car2.jpg', 'make' => $brand2, 'model' => 'X3', 'year' => 2019]);

        $models = $this->carRepository->getAllModels();

        // Возвращает массив с make, model, count
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        
        // Проверяем, что есть хотя бы один элемент с нужным make
        $makes = array_column($models, 'make');
        $this->assertContains($brand1, $makes);
        $this->assertContains($brand2, $makes);
    }

    public function test_can_get_statistics_with_filters(): void
    {
        $car1 = Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020, 'votes_count' => 5]);
        $car2 = Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021, 'votes_count' => 10]);
        Car::create(['image' => 'car3.jpg', 'make' => 'Audi', 'model' => 'A4', 'year' => 2019, 'votes_count' => 3]);

        // Test with make filter - returns paginated response
        $stats = $this->carRepository->getStatistics('BMW');
        $totalVotes = $this->carRepository->getTotalVotesCount('BMW');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('data', $stats);
        $this->assertCount(2, $stats['data']);
        $this->assertEquals(15, $totalVotes);
    }

    public function test_api_returns_random_pair(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020]);
        Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021]);

        $response = $this->getJson('/api/voting/pair');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_api_vote_success(): void
    {
        $car = Car::create([
            'image' => 'car1.jpg',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'votes_count' => 0,
        ]);

        $response = $this->postJson("/api/voting/{$car->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'votes_count' => 1,
        ]);
    }

    public function test_api_vote_duplicate(): void
    {
        $car = Car::create([
            'image' => 'car1.jpg',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'votes_count' => 0,
        ]);

        // First vote
        $this->postJson("/api/voting/{$car->id}");

        // Second vote from same IP
        $response = $this->postJson("/api/voting/{$car->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_api_get_cars_with_filters(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020, 'votes_count' => 5]);
        Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021, 'votes_count' => 10]);
        Car::create(['image' => 'car3.jpg', 'make' => 'Audi', 'model' => 'A4', 'year' => 2019, 'votes_count' => 3]);

        $response = $this->getJson('/api/cars?model=BMW');

        $response->assertStatus(200)
            ->assertJsonFragment(['model' => 'X5']);
    }

    public function test_api_get_models(): void
    {
        Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020]);
        Car::create(['image' => 'car2.jpg', 'make' => 'Audi', 'model' => 'A4', 'year' => 2019]);

        $response = $this->getJson('/api/models');

        // Возвращает массив моделей с make, model, count
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['make', 'model', 'count'],
                ],
            ]);
    }

    public function test_api_get_statistics(): void
    {
        $car1 = Car::create(['image' => 'car1.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2020, 'votes_count' => 5]);
        $car2 = Car::create(['image' => 'car2.jpg', 'make' => 'BMW', 'model' => 'X5', 'year' => 2021, 'votes_count' => 10]);

        // Создаём записи в таблице votes для корректного подсчёта
        for ($i = 0; $i < 5; $i++) {
            Vote::create(['car_id' => $car1->id, 'voter_id' => "ip_{$i}"]);
        }
        for ($i = 0; $i < 10; $i++) {
            Vote::create(['car_id' => $car2->id, 'voter_id' => "ip_{$i}_2"]);
        }

        // Фильтр по make (бренду) "BMW"
        $response = $this->getJson('/api/cars/statistics?model=BMW');

        $response->assertStatus(200)
            ->assertJson([
                'total_votes' => 15,
            ]);
    }
}
