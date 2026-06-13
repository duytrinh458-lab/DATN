<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY category_id INT(11) NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id', 'fk_products_category')
                ->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('fk_products_category');
        });

        DB::statement('ALTER TABLE products MODIFY category_id INT(11) NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id', 'fk_products_category')
                ->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
};