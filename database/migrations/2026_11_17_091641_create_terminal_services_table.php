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
        // A sellable slot on a terminal: a fuel grade bound to a dispenser station (pump).
        Schema::create('terminal_services', function (Blueprint $table) {
            // Primary key mirrors the usrbb.ru service id (transactions.service_id).
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('terminal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('station_id')->nullable();
            $table->string('station_name')->nullable();
            $table->string('name')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_services');
    }
};
