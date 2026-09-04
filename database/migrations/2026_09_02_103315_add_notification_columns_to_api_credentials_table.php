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
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->unsignedInteger('notify_limit')->nullable();
            $table->timestamp('notify_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->dropColumn(['notify_limit', 'notify_sent_at']);
        });
    }
};
