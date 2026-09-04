<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_ratelimits', function (Blueprint $table) {
            $table->uuid('measurement_id')->nullable()->after('measured_at');
            $table->index(['api_credential_id', 'measurement_id']);
        });
    }

    public function down(): void
    {
        Schema::table('account_ratelimits', function (Blueprint $table) {
            $table->dropIndex(['api_credential_id', 'measurement_id']);
            $table->dropColumn('measurement_id');
        });
    }
};
