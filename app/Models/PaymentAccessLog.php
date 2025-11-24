<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccessLog extends Model
{
    protected $fillable = [
        'user_id', 'payment_id', 'action', 'ip', 'user_agent', 'success'
    ];
}
