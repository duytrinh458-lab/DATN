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
    Schema::table('orders', function (Blueprint $table) {

        $table->string('shipping_full_name')->nullable();
        $table->string('shipping_phone')->nullable();

        $table->string('shipping_province')->nullable();
        $table->string('shipping_district')->nullable();
        $table->string('shipping_ward')->nullable();

        $table->text('shipping_street')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
