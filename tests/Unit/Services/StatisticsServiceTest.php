<?php

namespace Tests\Unit\Services;

use App\Models\Car;
use App\Models\Vote;
use App\Repositories\CarRepository;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsService $statisticsService;
    private CarRepository $carRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carRepository = new CarRepository(new Car());
        $this->statisticsService = new StatisticsService($this->carRepository);
    }

    public function test_get_statistics_returns_paginated_results(): void
    {
        Car::factory()->count(15)->create(['make' => 'BMW', 'year' => 2020]);

        $result = $this->statisticsService->getStatistics(null, null, null, 1, 10);

        $this->assertEquals(10, $result['data']->count());
        $this->assertEquals(15, $result['total']);
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(2, $result['last_page']);
    }

    public function test_get_statistics_filters_by_make(): void
    {
        Car::factory()->count(5)->create(['make' => 'BMW', 'year' => 2020]);
        Car::factory()->count(5)->create(['make' => 'Audi', 'year' => 2020]);

        $result = $this->statisticsService->getStatistics('BMW', null, null, 1, 10);

        $this->assertEquals(5, $result['data']->count());
        $this->assertEquals(5, $result['total']);
        $result['data']->each(fn ($car) => $this->assertEquals('BMW', $car->make));
    }

    public function test_get_statistics_filters_by_year_from(): void
    {
        Car::factory()->create(['make' => 'BMW', 'year' => 2018]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2019]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2021]);

        $result = $this->statisticsService->getStatistics(null, 2020, null, 1, 10);

        $this->assertEquals(2, $result['total']);
    }

    public function test_get_statistics_filters_by_year_to(): void
    {
        Car::factory()->create(['make' => 'BMW', 'year' => 2018]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2019]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2021]);

        $result = $this->statisticsService->getStatistics(null, null, 2019, 1, 10);

        $this->assertEquals(2, $result['total']);
    }

    public function test_get_statistics_filters_by_year_range(): void
    {
        Car::factory()->create(['make' => 'BMW', 'year' => 2017]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2018]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2019]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        Car::factory()->create(['make' => 'BMW', 'year' => 2021]);

        $result = $this->statisticsService->getStatistics(null, 2018, 2020, 1, 10);

        $this->assertEquals(3, $result['total']);
    }

    public function test_get_statistics_filters_by_make_and_year_range(): void
    {
        Car::factory()->count(2)->create(['make' => 'BMW', 'year' => 2018]);
        Car::factory()->count(3)->create(['make' => 'BMW', 'year' => 2019]);
        Car::factory()->count(2)->create(['make' => 'Audi', 'year' => 2019]);

        $result = $this->statisticsService->getStatistics('BMW', 2018, 2019, 1, 10);

        $this->assertEquals(5, $result['total']);
    }

    public function test_get_statistics_includes_votes_count(): void
    {
        $car = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        Vote::factory()->count(3)->create(['car_id' => $car->id]);

        $result = $this->statisticsService->getStatistics('BMW', null, null, 1, 10);

        $this->assertEquals(3, $result['data'][0]->votes_count);
    }

    public function test_get_statistics_orders_by_votes_count_desc(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        $car2 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        $car3 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);

        Vote::factory()->count(5)->create(['car_id' => $car1->id]);
        Vote::factory()->count(10)->create(['car_id' => $car2->id]);
        Vote::factory()->count(3)->create(['car_id' => $car3->id]);

        $result = $this->statisticsService->getStatistics('BMW', null, null, 1, 10);

        $this->assertEquals($car2->id, $result['data'][0]->id);
        $this->assertEquals($car1->id, $result['data'][1]->id);
        $this->assertEquals($car3->id, $result['data'][2]->id);
    }

    public function test_get_total_votes_count_returns_correct_count(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        $car2 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);

        Vote::factory()->count(5)->create(['car_id' => $car1->id]);
        Vote::factory()->count(3)->create(['car_id' => $car2->id]);

        $total = $this->statisticsService->getTotalVotesCount('BMW', null, null);

        $this->assertEquals(8, $total);
    }

    public function test_get_total_votes_count_filters_by_make(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        $car2 = Car::factory()->create(['make' => 'Audi', 'year' => 2020]);

        Vote::factory()->count(5)->create(['car_id' => $car1->id]);
        Vote::factory()->count(3)->create(['car_id' => $car2->id]);

        $total = $this->statisticsService->getTotalVotesCount('BMW', null, null);

        $this->assertEquals(5, $total);
    }

    public function test_get_total_votes_count_filters_by_year_range(): void
    {
        $car1 = Car::factory()->create(['make' => 'BMW', 'year' => 2019]);
        $car2 = Car::factory()->create(['make' => 'BMW', 'year' => 2020]);
        $car3 = Car::factory()->create(['make' => 'BMW', 'year' => 2021]);

        Vote::factory()->count(5)->create(['car_id' => $car1->id]);
        Vote::factory()->count(3)->create(['car_id' => $car2->id]);
        Vote::factory()->count(2)->create(['car_id' => $car3->id]);

        $total = $this->statisticsService->getTotalVotesCount(null, 2019, 2020);

        $this->assertEquals(8, $total);
    }

    public function test_get_all_makes_returns_unique_makes(): void
    {
        Car::factory()->count(3)->create(['make' => 'BMW']);
        Car::factory()->count(2)->create(['make' => 'Audi']);
        Car::factory()->count(1)->create(['make' => 'Ford']);

        $makes = $this->statisticsService->getAllMakes();

        $this->assertEquals(['Audi', 'BMW', 'Ford'], $makes);
    }

    public function test_get_all_makes_returns_empty_array_when_no_cars(): void
    {
        $makes = $this->statisticsService->getAllMakes();

        $this->assertEmpty($makes);
    }
}
