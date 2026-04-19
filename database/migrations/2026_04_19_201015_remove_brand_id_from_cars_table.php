<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('cars', 'brand_id')) {
            try {
                // Для MariaDB/MySQL
                DB::statement('ALTER TABLE cars DROP FOREIGN KEY IF EXISTS cars_brand_id_foreign');
                DB::statement('DROP INDEX IF EXISTS cars_brand_id_model_index ON cars');
            } catch (\Exception $e) {
                // Игнорируем ошибки если индексы не существуют (SQLite в тестах)
            }
            
            Schema::table('cars', function (Blueprint $table) {
                $table->dropColumn('brand_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable();
        });
    }
};
