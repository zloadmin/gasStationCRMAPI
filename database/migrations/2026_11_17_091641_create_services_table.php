
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id')->index();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('currency')->default(643);

            $table->string('good_name')->nullable();
            $table->unsignedInteger('good_tax')->default(0);
            $table->string('good_type')->nullable();

            $table->boolean('archived')->default(false);
            $table->json('data')->nullable();
            $table->string('yandex_type')->nullable();

            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('remote_updated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
