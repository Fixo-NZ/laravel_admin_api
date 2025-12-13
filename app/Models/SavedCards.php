<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class SavedCards extends Model
{
    protected $fillable = [
        'homeowner_id',
        'customer_id',
        'payment_method_id',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year'
    ];

    public function getCardBrandAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function getCardLast4numberAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}
