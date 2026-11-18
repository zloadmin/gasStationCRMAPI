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
        Schema::create('fuel_arrivals', function (Blueprint $table) {
            $table->id();

            $table->dateTime('date');
            $table->foreignId('service_id')->constrained('services');

            $table->decimal('quantity_kg', 12, 3)->comment('Количество топлива в кг');
            $table->decimal('volume_liters', 12, 3)->comment('Объем топлива в литрах');
            $table->decimal('density', 8, 4)->comment('Плотность топлива');

            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_arrivals');
    }
};
