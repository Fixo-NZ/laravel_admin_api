<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year',
    ];

    // ===============================
    // SAFE DECRYPT (DO NOT CRASH)
    // ===============================
    public function getCardBrandAttribute($value)
    {
        return $this->safeDecrypt($value);
    }

    public function getCardLast4numberAttribute($value)
    {
        return $this->safeDecrypt($value);
    }

    private function safeDecrypt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Value is not encrypted with Laravel (plaintext/corrupt/old key).
            // Return as-is so Filament can still display something.
            return $value;
        }
    }

    // ===============================
    // ENCRYPT ON WRITE (GOING FORWARD)
    // ===============================
    public function setCardBrandAttribute($value): void
    {
        $this->attributes['card_brand'] = $this->safeEncrypt($value);
    }

    public function setCardLast4numberAttribute($value): void
    {
        $this->attributes['card_last4number'] = $this->safeEncrypt($value);
    }

    private function safeEncrypt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If it already looks like a Laravel encrypted payload, store as-is
        // (prevents accidental double-encryption).
        if (is_string($value) && str_starts_with($value, 'eyJpdiI6')) {
            return $value;
        }

        return Crypt::encryptString((string) $value);
    }

    // ===============================
    // RELATIONSHIP
    // ===============================
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}
