<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRatelimit extends Model
{
    protected $fillable = [
        'limit_type',
        'limit',
        'remaining',
        'reset',
        'reset_time',
    ];
}
