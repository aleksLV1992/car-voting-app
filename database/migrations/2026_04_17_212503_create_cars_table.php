<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('image')->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable()->index();
            $table->integer('year')->nullable()->index();
            $table->integer('odometer')->nullable();
            $table->string('units')->nullable();
            $table->string('engine')->nullable();
            $table->string('transmission')->nullable();
            $table->string('color')->nullable();
            $table->string('brand')->nullable();
            $table->integer('winning_bid_amount')->nullable();
            $table->string('winning_bid_location')->nullable();
            $table->integer('votes_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
