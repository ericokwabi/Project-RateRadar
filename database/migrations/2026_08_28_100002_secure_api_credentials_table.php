<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_credentials', function (Blueprint $table) {
            // Versleutelde secrets zijn fors langer dan het origineel en passen
            // niet betrouwbaar in een varchar(255).
            $table->text('api_secret')->change();
        });

        Schema::table('api_credentials', function (Blueprint $table) {
            // Eén set sleutels per webshop; anders weet je niet welke telt.
            $table->unique('store_id');
        });
    }

    public function down(): void
    {
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->dropUnique(['store_id']);
            $table->string('api_secret')->change();
        });
    }
};
