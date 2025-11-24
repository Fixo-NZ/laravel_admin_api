<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'nullable|string',
        ]);

        try {
            // Set Stripe secret key from .env
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $request->amount * 100, // convert to cents
                'currency' => 'usd',
                'payment_method' => 'pm_card_visa',
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'expand' => ['payment_method', 'charges.data.payment_method_details'],
            ]);

            //agsave datoy yanti db

            $charge = $paymentIntent->payment_method->card ?? null;
            $encryptBrand = Crypt::encryptString($charge['brand']);
            $encryptLast4 = Crypt::encryptString($charge['last4']);
            $encExpYear = Crypt::encryptString($charge['exp_year']);
            $encExpMonth = Crypt::encryptString($charge['exp_month']);

            Log::info('User Authenticated?', [
                'user' => auth('sanctum')->user(),
                'id' => auth('sanctum')->id()
            ]);

            $payment = Payment::create([
                'homeowner_id' => auth('sanctum')->id(),
                'payment_method_id' => $paymentIntent->payment_method->id ?? $paymentIntent->payment_method,
                'amount' => $request->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'card_brand' => $encryptBrand ?? null,
                'card_last4number' => $encryptLast4 ?? null,
                'exp_month' => $encExpMonth ?? null,
                'exp_year' => $encExpYear ?? null,
            ]);

            return response()->json([
                'message' => 'Payment processed successfully',
                'amount' => $payment->amount,
                'status' => $paymentIntent->status,
                'card_brand' => $paymentIntent->payment_method->card->brand ?? null,
                'card_last4' => $paymentIntent->payment_method->card->last4 ?? null,
                'exp_month' => $paymentIntent->payment_method->card->exp_month ?? null,
                'exp_year' => $paymentIntent->payment_method->card->exp_year ?? null,
                'payment_method_id' => $paymentIntent->payment_method->id ?? $paymentIntent->payment_method,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function viewDecryptedPayment(Request $request, $id)
    {

        $user = $request->user();


        $payment = \App\Models\Payment::where('id', $id)
            ->where('homeowner_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or you do not have access to this record.',
            ], 404);
        }

        // Only allow if the user is the owner or an admin (basic security)
        if ($user->id !== $payment->homeowner_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        try {
            // Decrypt fields
            $decrypted = [
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'card_brand' => $payment->card_brand ? Crypt::decryptString($payment->card_brand) : null,
                'card_last4number' => $payment->card_last4number ? Crypt::decryptString($payment->card_last4number) : null,
                'exp_month' => $payment->exp_month ? Crypt::decryptString($payment->exp_month) : null,
                'exp_year' => $payment->exp_year ? Crypt::decryptString($payment->exp_year) : null,
            ];

            // Log access
            \App\Models\PaymentAccessLog::create([
                'homeowner_id' => $user->id,
                'payment_id' => $payment->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success' => true,
            ]);

            return response()->json([
                'message' => 'Decrypted data accessed successfully',
                'data' => $decrypted,
            ]);
        } catch (\Exception $e) {
            // Failed decryption or error
            \App\Models\PaymentAccessLog::create([
                'homeowner_id' => $user->id ?? null,
                'payment_id' => $payment->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success' => false,
            ]);

            return response()->json(['error' => 'Failed to decrypt data'], 500);
        }
    }
}
