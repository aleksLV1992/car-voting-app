<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CarSeeder extends Seeder
{
    public function run(): void
    {
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

    private function createCar(array $data): void
    {
        $image = $data['Image'] ?? null;

        if (!$image) {
            return;
        }

        $imagePath = 'cars/' . $image;

        Car::firstOrCreate(
            ['image' => $imagePath],
            [
                'make' => $data['Make'] ?? $data['Brand'] ?? null,
                'model' => $data['Model'] ?? null,
                'year' => $data['Year'] ?? null,
                'odometer' => $data['Odometer'] ?? null,
                'units' => $data['Units'] ?? null,
                'engine' => $data['Engine'] ?? null,
                'transmission' => $data['Transmission'] ?? null,
                'color' => $data['Color'] ?? null,
                'winning_bid_amount' => $data['WinningBidAmount'] ?? null,
                'winning_bid_location' => $data['WinningBidLocation'] ?? null,
                'votes_count' => 0,
            ]
        );
    }
}
