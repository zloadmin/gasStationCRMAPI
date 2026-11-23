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
        Schema::create('transactions', function (Blueprint $table) {
            // Primary key mirrors the usrbb.ru transaction id.
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('tid'); // Per-terminal sequence number
            $table->foreignId('terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->foreignId('terminal_service_id')->nullable()->constrained('terminal_services')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->string('type')->index(); // payment_cash, payment_card, ...

            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->string('station_name')->nullable()->index();


            // Money
            $table->decimal('price', 12, 2); // Order total
            $table->decimal('inserted', 12, 2)->default(0); // Paid by the customer
            $table->decimal('dispensed', 12, 2)->default(0); // Change given back
            $table->decimal('price_returned', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->unsignedSmallInteger('currency')->default(643); // ISO 4217 numeric code, 643 = RUB

            // Fuel
            $table->decimal('service_price', 12, 2)->nullable(); // Price per litre applied
            $table->decimal('amount', 12, 2)->nullable(); // Litres ordered
            $table->decimal('amount_dispensed', 12, 2)->nullable(); // Litres actually pumped


            $table->boolean('printed')->default(false);
            $table->boolean('sync')->default(false);
            $table->boolean('no_troubles')->default(true);
            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('user_id')->nullable(); // usrbb.ru user id, not a local users.id

            $table->timestamp('remote_created_at')->nullable()->index();
            $table->timestamps();

            $table->json('fisc')->nullable();
            $table->json('service_json')->nullable();
            $table->json('data')->nullable();

            $table->boolean('parsed_full')->default(false)->index();




            // fuel_arrival table data
            $table->decimal('fuel_arrival_price_per_liter_cashless', 14)->nullable();
            $table->decimal('fuel_arrival_price_per_liter_cash', 14)->nullable();
            $table->decimal('fuel_arrival_price_per_liter', 14)->nullable();

            // profit calc
            $table->string('profit_service_name')->nullable();
            $table->decimal('profit_revenue', 14)->nullable();
            $table->decimal('profit_cost', 14)->nullable();
            $table->decimal('profit_profit', 14)->nullable();




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
