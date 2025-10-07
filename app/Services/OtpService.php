<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    public function generateOtp($phone)
    {
        $otpCode = rand(100000, 999999);

        $otp = Otp::create([
            'phone' => $phone,
            'otp_code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        //$this->sendOtp($phone, $otpCode);

        return $otp;
    }

    public function verifyOtp($phone, $otpCode)
    {
        $otp = Otp::where('phone', $phone)
            ->where('otp_code', $otpCode)
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otp) {
            $otp->is_verified = true;
            $otp->save();

            return true;
        }

        return false;
    }

    public function isVerified($phone)
    {
        $latestOtp = Otp::where('phone', $phone)
            ->where('is_verified', true)
            ->latest()
            ->first();

        return $latestOtp && $latestOtp->is_verified;
    }

    public function sendOtp($phone, $otp)
    {
        $recipient = env('OTP_EMAIL', 'dummy-email@example.com');

        Mail::raw("Your OTP for phone $phone is: $otp", function ($message) use ($recipient) {
            $message->to($recipient)->subject('Your OTP Code');
        });
    }
}