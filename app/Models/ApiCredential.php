<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCredential extends Model
{
    protected $fillable = [
        'store_id',
        'api_key',
        'api_secret',
    ];
}
