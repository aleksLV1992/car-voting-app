<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // Сначала создадим бренды из уникальных значений
        $this->createBrands();

        $dataPath = database_path('data');
        $altDataPath = base_path('data');

        if (!File::exists($dataPath) && !File::exists($altDataPath)) {
            $this->command->error('Data directory not found');
            return;
        }

        $dataPath = File::exists($dataPath) ? $dataPath : $altDataPath;
        $importedCount = 0;

        foreach (File::files($dataPath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $data = json_decode(File::get($file->getPathname()), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            if (isset($data['Image'])) {
                $this->createCar($data);
                $importedCount++;
            } elseif (isset($data['Data']) && is_array($data['Data'])) {
                foreach ($data['Data'] as $carData) {
                    $this->createCar($carData);
                    $importedCount++;
                }
            }
        }

        $this->command->info("Successfully imported {$importedCount} cars.");
    }

    private function createBrands(): void
    {
        $dataPath = database_path('data');
        $altDataPath = base_path('data');
        $dataPath = File::exists($dataPath) ? $dataPath : $altDataPath;

        $brands = [];

        foreach (File::files($dataPath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $data = json_decode(File::get($file->getPathname()), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            $processCarData = function($carData) use (&$brands) {
                $brand = $carData['Brand'] ?? null;
                if ($brand && !isset($brands[$brand])) {
                    $brands[$brand] = [
                        'name' => $brand,
                        'slug' => Str::slug($brand),
                    ];
                }
            };

            if (isset($data['Image'])) {
                $processCarData($data);
            } elseif (isset($data['Data']) && is_array($data['Data'])) {
                foreach ($data['Data'] as $carData) {
                    $processCarData($carData);
                }
            }
        }

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(
                ['slug' => $brandData['slug']],
                ['name' => $brandData['name']]
            );
        }

        $this->command->info("Created " . count($brands) . " brands.");
    }

    private function createCar(array $data): void
    {
        $image = $data['Image'] ?? null;

        if (!$image) {
            return;
        }

        $imagePath = 'cars/' . $image;
        $brandName = $data['Brand'] ?? null;
        $brandId = null;

        if ($brandName) {
            $brand = Brand::where('name', $brandName)->first();
            $brandId = $brand?->id;
        }

        Car::firstOrCreate(
            ['image' => $imagePath],
            [
                'make' => $data['Make'] ?? null,
                'model' => $data['Model'] ?? null,
                'year' => $data['Year'] ?? null,
                'odometer' => $data['Odometer'] ?? null,
                'units' => $data['Units'] ?? null,
                'engine' => $data['Engine'] ?? null,
                'transmission' => $data['Transmission'] ?? null,
                'color' => $data['Color'] ?? null,
                'brand' => $brandName,
                'brand_id' => $brandId,
                'winning_bid_amount' => $data['WinningBidAmount'] ?? null,
                'winning_bid_location' => $data['WinningBidLocation'] ?? null,
                'votes_count' => 0,
            ]
        );
    }
}
