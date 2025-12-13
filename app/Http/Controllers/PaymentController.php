<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Payment;
use App\Models\SavedCards;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {

        try {
            // Set Stripe secret key from .env
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $user = $request->user();
            // Step 1: Check if the user already has a Stripe customer via savedCards table
            $existingPayment = SavedCards::where('homeowner_id', $user->id)
                ->whereNotNull('customer_id')
                ->first();

            if ($existingPayment) {
                // Retrieve the Stripe Customer from the existing payment
                $customer = \Stripe\Customer::retrieve($existingPayment->customer_id);
            } else {
                // Create a new Stripe Customer
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                ]);
            }

            $setupIntent = \Stripe\SetupIntent::create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
            ]);

            return response()->json([
                'client_secret' => $setupIntent->client_secret,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function savePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = $request->user();
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Step 1: Retrieve the Stripe Customer from the savedCars table
            $existingPayment = SavedCards::where('homeowner_id', $user->id)
                ->whereNotNull('customer_id')
                ->first();

            if ($existingPayment) {
                $customerId = $existingPayment->customer_id;
                $customer = \Stripe\Customer::retrieve($customerId);
            } else {
                // Safety check: If somehow no customer exists, create one
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                ]);
                $customerId = $customer->id;
            }

            // Step 2: Retrieve the PaymentMethod
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);

            // Step 3: Attach the PaymentMethod to the Customer if not already attached
            if (!$paymentMethod->customer) {
                $paymentMethod->attach([
                    'customer' => $customerId,
                ]);
            }

            // Step 4: Encrypt card details
            $encryptBrand = Crypt::encryptString($paymentMethod->card->brand);
            $encryptLast4 = Crypt::encryptString($paymentMethod->card->last4);
            $encExpMonth = Crypt::encryptString($paymentMethod->card->exp_month);
            $encExpYear = Crypt::encryptString($paymentMethod->card->exp_year);

            // Step 5: Save the payment method in DB
            $payment = SavedCards::create([
                'homeowner_id' => $user->id,
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethod->id,
                'card_brand' => $encryptBrand,
                'card_last4number' => $encryptLast4,
                'exp_month' => $encExpMonth,
                'exp_year' => $encExpYear,
            ]);

            return response()->json([
                'message' => 'Payment method saved successfully',
                'payment' => $payment,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function chargeSavedCard(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'nullable|string|size:3',
        ]);

        $user = $request->user();
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $request->amount * 100,
            'currency' => $request->currency ?? 'usd',
            'customer' => 'cus_Tb3aecQvErAbBv',
            'payment_method' => 'pm_1SdrTCJO8ywAD8tRtKAm1Xqw',
            'off_session' => true, // charge saved card
            'confirm' => true,
        ]);

        $paymentMethod = \Stripe\PaymentMethod::retrieve(
            $paymentIntent->payment_method
        );

        $payment = Payment::create([
            'homeowner_id' => $user->id,
            'customer_id' => $paymentIntent->customer,
            'payment_method_id' => $paymentIntent->payment_method,
            'amount' => $request->amount,
            'currency' => 'NZD',
            'card_brand' => $paymentMethod->card->brand,
            'card_last4number' => $paymentMethod->card->last4,
            'status' => $paymentIntent->status,
        ]);

        return response()->json([
            'message' => 'Payment successful',
            'payment_intent' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);
    }


    public function deletePayment(Request $request, $id)
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

        // Only allow if the user is the owner
        if ($user->id !== $payment->homeowner_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }
    public function updatePayment(Request $request, $id)
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

        // Only allow if the user is the owner
        if ($user->id !== $payment->homeowner_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3',
            'card_brand' => 'required|string|max:40',
            'card_last4number' => 'required|numeric|digits:4',
            'exp_month' => 'required|string|size:2',
            'exp_year' => 'required|string|size:4',
        ]);

        if ($validated['exp_month'] < 1 || $validated['exp_month'] > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Expiration month must be between 01 and 12.',
            ], 400);
        } elseif ($validated['exp_year'] < date('Y')) {
            return response()->json([
                'success' => false,
                'message' => 'Expiration year cannot be in the past.',
            ], 400);
        } elseif ($validated['exp_year'] == date('Y') && $validated['exp_month'] < date('m')) {
            return response()->json([
                'success' => false,
                'message' => 'Expiration month cannot be in the past for the current year.',
            ], 400);
        } elseif ($validated['exp_year'] > date('Y') + 20) {
            return response()->json([
                'success' => false,
                'message' => 'Expiration year is too far in the future.',
            ], 400);
        }

        // Encrypt sensitive fields if they are being updated
        if (isset($validated['card_brand'])) {
            $validated['card_brand'] = Crypt::encryptString($validated['card_brand']);
        }
        if (isset($validated['card_last4number'])) {
            $validated['card_last4number'] = Crypt::encryptString($validated['card_last4number']);
        }
        if (isset($validated['exp_month'])) {
            $validated['exp_month'] = Crypt::encryptString($validated['exp_month']);
        }
        if (isset($validated['exp_year'])) {
            $validated['exp_year'] = Crypt::encryptString($validated['exp_year']);
        }

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'data' => $payment,
        ]);
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

        if ($user->id !== $payment->homeowner_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Initialize decrypted array
        $decrypted = [
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'card_brand' => null,
            'card_last4number' => null,
            'exp_month' => null,
            'exp_year' => null,
        ];

        try {
            // Attempt decryption for each field individually
            if ($payment->card_brand) {
                try {
                    $decrypted['card_brand'] = Crypt::decryptString($payment->card_brand);
                } catch (\Exception $e) {
                    $decrypted['card_brand'] = $payment->card_brand; // fallback to raw
                }
            }
            if ($payment->card_last4number) {
                try {
                    $decrypted['card_last4number'] = Crypt::decryptString($payment->card_last4number);
                } catch (\Exception $e) {
                    $decrypted['card_last4number'] = $payment->card_last4number;
                }
            }
            if ($payment->exp_month) {
                try {
                    $decrypted['exp_month'] = Crypt::decryptString($payment->exp_month);
                } catch (\Exception $e) {
                    $decrypted['exp_month'] = $payment->exp_month;
                }
            }
            if ($payment->exp_year) {
                try {
                    $decrypted['exp_year'] = Crypt::decryptString($payment->exp_year);
                } catch (\Exception $e) {
                    $decrypted['exp_year'] = $payment->exp_year;
                }
            }

            // Log successful access
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
            // Log failed access
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
