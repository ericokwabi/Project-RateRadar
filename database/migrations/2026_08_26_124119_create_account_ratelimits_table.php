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
        Schema::create('account_ratelimits', function (Blueprint $table) {
            $table->id();
            $table->string('limit_type');
            $table->integer('limit');
            $table->integer('remaining');
            $table->integer('reset');
            $table->timestamp('reset_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_ratelimits');
    }
};
