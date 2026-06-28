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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->unique();
            $table->string('name');
            $table->string('sku', 100)->nullable()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('og_image', 512)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('equipment_state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('equipment_availability_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_status_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('show_price')->default(true);
            $table->text('price_comment')->nullable();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
