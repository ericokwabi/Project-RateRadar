<?php

namespace App\Services;

use App\Mail\RateLimitExceeded;
use App\Models\AccountRatelimit;
use App\Models\ApiCredential;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class RateLimitAlertService
{
    private const CONSECUTIVE_HITS = 4;

    private const COOLDOWN_MINUTES = 30;

    public function check(ApiCredential $credential, Carbon $measuredAt): bool
    {
        $hits = AccountRatelimit::query()
            ->where('api_credential_id', $credential->id)
            ->where('limit_type', 'limit5Min')
            ->whereNotNull('measurement_id')
            ->orderByDesc('measured_at')
            ->get()
            ->filter(fn (AccountRatelimit $row) => $row->measured_at !== null)
            ->groupBy(fn (AccountRatelimit $row): string => $row->measurement_id)
            ->take(self::CONSECUTIVE_HITS)
            ->values();

        $qualifies = $hits->count() === self::CONSECUTIVE_HITS
            && $hits->every(fn ($group) => $group->first()->hit_429);

        if (! $qualifies) {
            if ($credential->notify_limit !== null) {
                $credential->forceFill(['notify_limit' => null])->save();
            }

            return false;
        }

        $credential->forceFill(['notify_limit' => self::CONSECUTIVE_HITS])->save();

        $lastSentAt = $credential->notify_sent_at;

        if ($lastSentAt !== null && $lastSentAt->greaterThan(Carbon::now()->subMinutes(self::COOLDOWN_MINUTES))) {
            return false;
        }

        $recipient = config('services.rate_limit_alert.email');

        if (blank($recipient)) {
            return false;
        }

        Mail::to($recipient)->send(new RateLimitExceeded($credential, $measuredAt));
        $credential->forceFill(['notify_sent_at' => Carbon::now()])->save();

        return true;
    }
}
