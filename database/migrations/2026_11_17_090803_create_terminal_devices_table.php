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
        Schema::create('terminal_devices', function (Blueprint $table) {
            // Primary key mirrors the usrbb.ru device id.
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('terminal_id')->constrained()->cascadeOnDelete();
            $table->string('device_name')->index();
            $table->json('last_report')->nullable();
            $table->timestamp('last_report_at')->nullable();
            $table->unsignedBigInteger('last_report_time')->nullable();
            $table->text('last_error')->nullable();
            $table->boolean('from_device')->default(false);

            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('remote_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_devices');
    }
};
