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
            $table->foreignId('terminal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('terminal_services')->nullOnDelete();
            $table->string('type')->index(); // payment_cash, payment_card, ...

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

            $table->json('fisc')->nullable();
            $table->boolean('printed')->default(false);
            $table->boolean('sync')->default(false);
            $table->boolean('no_troubles')->default(true);
            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('user_id')->nullable(); // usrbb.ru user id, not a local users.id

            $table->timestamp('remote_created_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['terminal_id', 'tid']);
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
