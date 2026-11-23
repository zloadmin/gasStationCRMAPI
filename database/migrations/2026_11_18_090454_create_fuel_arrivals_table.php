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
            $table->foreignId('service_id')->index()->constrained('services');
            $table->decimal('quantity_kg', 12, 3)->comment('Масса, кг');
            $table->decimal('volume_liters', 12, 3)->comment('Объем, л');
            $table->decimal('total_price', 14)->comment('Общая стоимость');
            $table->decimal('price_per_liter_cashless', 14)->comment('Цена за литр (безнал)');
            $table->decimal('price_per_liter_cash', 14)->comment('Цена за литр (наличные)');

            $table->timestamps();

            $table->index('date');
            $table->index(['service_id', 'date', 'id']);
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
