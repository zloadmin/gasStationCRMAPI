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
        Schema::create('organizations', function (Blueprint $table) {
            // Primary key mirrors the usrbb.ru organization id (referenced as org_id elsewhere).
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->default('default');
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);

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
        Schema::dropIfExists('organizations');
    }
};
