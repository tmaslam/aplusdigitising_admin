<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTopup extends Model
{
    protected $table = 'customer_topups';

    protected $fillable = [
        'site_id',
        'user_id',
        'website',
        'amount',
        'plan_option',
        'status',
        'stripe_reference',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'completed_at' => 'datetime',
    ];
}
