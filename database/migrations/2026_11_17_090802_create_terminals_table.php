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
        Schema::create('terminals', function (Blueprint $table) {
            // Primary key mirrors the usrbb.ru terminal id.
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('secret')->nullable();
            $table->string('type')->default('default');
            $table->boolean('archived')->default(false);

            $table->json('terminal_info')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('settings_updated_at')->nullable();
            $table->string('last_query')->nullable();
            $table->timestamp('last_query_at')->nullable();

            $table->boolean('yandex_enabled')->default(false);
            $table->boolean('yandex_test')->default(false);
            $table->string('yandex_token')->nullable();
            $table->string('yandex_name')->nullable();
            $table->string('yandex_address')->nullable();
            $table->json('yandex_location')->nullable();
            $table->boolean('yandex_autooplata_enabled')->default(false);

            $table->unsignedBigInteger('place_id')->nullable()->index();
            $table->json('ping_settings')->nullable();
            $table->timestamp('ping_at')->nullable();

            $table->boolean('loyal_client_enabled')->default(false);
            $table->string('loyal_client_api')->nullable();
            $table->string('loyal_client_point')->nullable();

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
        Schema::dropIfExists('terminals');
    }
};
