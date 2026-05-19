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

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT THÔNG TIN NGƯỜI NHẬN
            |--------------------------------------------------------------------------
            */

            $table->string('shipping_full_name')
                ->nullable()
                ->after('address_id');

            $table->string('shipping_phone')
                ->nullable()
                ->after('shipping_full_name');

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT ĐỊA CHỈ
            |--------------------------------------------------------------------------
            */

            $table->string('shipping_province')
                ->nullable()
                ->after('shipping_phone');

            $table->string('shipping_district')
                ->nullable()
                ->after('shipping_province');

            $table->string('shipping_ward')
                ->nullable()
                ->after('shipping_district');

            $table->text('shipping_street')
                ->nullable()
                ->after('shipping_ward');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([

                'shipping_full_name',

                'shipping_phone',

                'shipping_province',

                'shipping_district',

                'shipping_ward',

                'shipping_street',
            ]);
        });
    }
};