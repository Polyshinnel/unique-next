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
        $this->addIndex('products', ['published_at', 'id'], 'products_published_at_id_index');
        $this->addIndex('products', ['category_id', 'published_at'], 'products_category_id_published_at_index');
        $this->addIndex('products', ['product_status_id', 'published_at'], 'products_product_status_id_published_at_index');
        $this->addIndex('products', ['equipment_availability_id', 'published_at'], 'products_equipment_availability_id_published_at_index');
        $this->addIndex('products', ['equipment_state_id', 'published_at'], 'products_equipment_state_id_published_at_index');
        $this->addIndex('products', ['show_price', 'price'], 'products_show_price_price_index');
        $this->addIndex('product_region', ['region_id', 'product_id'], 'product_region_region_id_product_id_index');
        $this->addIndex('product_tag', ['tag_id', 'product_id'], 'product_tag_tag_id_product_id_index');
        $this->addIndex('tags', ['name'], 'tags_name_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndex('tags', 'tags_name_index');

        $this->dropIndex('product_tag', 'product_tag_tag_id_product_id_index');
        $this->dropIndex('product_region', 'product_region_region_id_product_id_index');
        $this->dropIndex('products', 'products_show_price_price_index');
        $this->dropIndex('products', 'products_equipment_state_id_published_at_index');
        $this->dropIndex('products', 'products_equipment_availability_id_published_at_index');
        $this->dropIndex('products', 'products_product_status_id_published_at_index');
        $this->dropIndex('products', 'products_category_id_published_at_index');
        $this->dropIndex('products', 'products_published_at_id_index');
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndex(string $table, array $columns, string $name): void
    {
        if (Schema::hasIndex($table, $name) || Schema::hasIndex($table, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name): void {
            $table->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($name): void {
            $table->dropIndex($name);
        });
    }
};
