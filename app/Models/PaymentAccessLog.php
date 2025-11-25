<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccessLog extends Model
{
    protected $fillable = [
        'homeowner_id', 'payment_id', 'action', 'ip', 'user_agent', 'success'
    ];
}
