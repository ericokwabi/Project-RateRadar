<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_ratelimits', function (Blueprint $table) {
            // Bij welke webshop hoort deze meting? Null = de sleutel uit .env.
            $table->unsignedBigInteger('api_credential_id')->nullable()->after('id');

            // De drie vensters van één meting delen dezelfde measured_at, zodat
            // ze later weer als één meetpunt uit de database komen.
            $table->timestamp('measured_at')->nullable()->after('api_credential_id');

            // Losstaand van remaining: de API kan 429 geven terwijl remaining
            // nog niet op nul stond.
            $table->boolean('hit_429')->default(false)->after('remaining');

            $table->index(['api_credential_id', 'measured_at']);
            $table->index('measured_at');
        });

        // Rijen van vóór deze migratie hebben nog geen measured_at.
        DB::table('account_ratelimits')->whereNull('measured_at')->update([
            'measured_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('account_ratelimits', function (Blueprint $table) {
            $table->dropIndex(['api_credential_id', 'measured_at']);
            $table->dropIndex(['measured_at']);
            $table->dropColumn(['api_credential_id', 'measured_at', 'hit_429']);
        });
    }
};
