<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SavedCards;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Create a SetupIntent for saving payment methods
     * Called by: POST /payment/process
     * 
     * NOTE: This only creates a SetupIntent and returns client_secret.
     * It does NOT save anything to the database yet - that happens in savePaymentMethod()
     */
    public function processPayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $user = $request->user();

            // Get existing customer ID, or create new customer
            $saved = SavedCards::where('homeowner_id', $user->id)->first();

            if ($saved && $saved->customer_id) {
                $customerId = $saved->customer_id;
                Log::info('Using existing Stripe customer', ['customer_id' => $customerId]);
            } else {
                // Create new Stripe customer (will be linked to SavedCards in savePaymentMethod)
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name'  => $user->first_name . ' ' . $user->last_name,
                ]);
                $customerId = $customer->id;
                Log::info('Created new Stripe customer', ['customer_id' => $customerId, 'user_id' => $user->id]);
            }

            // Create SetupIntent for saving card
            $setupIntent = SetupIntent::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
            ]);

            Log::info('SetupIntent created successfully', [
                'customer_id' => $customerId,
                'client_secret' => $setupIntent->client_secret,
            ]);

            return response()->json([
                'client_secret' => $setupIntent->client_secret,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe Card Error in processPayment', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('SetupIntent Error in processPayment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save a payment method (called after user confirms card)
     * Called by: POST /payments/save-payment-method
     */
    public function savePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'card_holder' => 'nullable|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $user = $request->user();

            // Get customer from saved cards
            $saved = SavedCards::where('homeowner_id', $user->id)->first();
            if (!$saved || !$saved->customer_id) {
                Log::warning('Customer not found for user', ['user_id' => $user->id]);
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $customerId = $saved->customer_id;

            // Retrieve payment method from Stripe
            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);

            // Attach to customer if not already attached
            if (!$paymentMethod->customer) {
                $paymentMethod->attach([
                    'customer' => $customerId,
                ]);
            }

            // Extract card details
            $cardBrand = $paymentMethod->card->brand ?? null;
            $cardLast4 = $paymentMethod->card->last4 ?? null;
            $expMonth = $paymentMethod->card->exp_month ?? null;
            $expYear = $paymentMethod->card->exp_year ?? null;

            // Encrypt sensitive card details
            $encryptedBrand = $cardBrand ? Crypt::encryptString($cardBrand) : null;
            $encryptedLast4 = $cardLast4 ? Crypt::encryptString($cardLast4) : null;
            $encryptedExpMonth = $expMonth ? Crypt::encryptString((string)$expMonth) : null;
            $encryptedExpYear = $expYear ? Crypt::encryptString((string)$expYear) : null;

            // Save or update card in database
            $savedCard = SavedCards::updateOrCreate(
                ['homeowner_id' => $user->id],
                [
                    'customer_id' => $customerId,
                    'payment_method_id' => $paymentMethod->id,
                    'card_brand' => $encryptedBrand,
                    'card_last4number' => $encryptedLast4,
                    'exp_month' => $encryptedExpMonth,
                    'exp_year' => $encryptedExpYear,
                ]
            );

            Log::info('Payment method saved', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'card_last4' => $cardLast4,
            ]);

            // Return response in format that matches PaymentModel
            return response()->json([
                'message' => 'Payment method saved successfully',
                'payment' => [
                    'id' => (string) $savedCard->id,
                    'status' => 'saved',
                    'card_brand' => $cardBrand,
                    'card_last4number' => $cardLast4,
                    'created_at' => $savedCard->created_at->toIso8601String(),
                ]
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe Card Error in savePaymentMethod', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Error saving payment method', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Charge a saved card
     * Called by: POST /payment/charge-saved-card
     */
    public function chargeSavedCard(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'nullable|string|size:3',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $user = $request->user();

            // Get saved card
            $saved = SavedCards::where('homeowner_id', $user->id)->first();
            if (!$saved || !$saved->payment_method_id) {
                return response()->json(['error' => 'No saved card found'], 404);
            }

            // Create PaymentIntent
            $intent = PaymentIntent::create([
                'amount' => (int) ($request->amount * 100),
                'currency' => $request->currency ?? 'nzd',
                'customer' => $saved->customer_id,
                'payment_method' => $saved->payment_method_id,
                'off_session' => true,
                'confirm' => true,
            ]);

            // Get payment method details for logging
            $paymentMethod = PaymentMethod::retrieve($saved->payment_method_id);

            // Save transaction
            $payment = Payment::create([
                'homeowner_id' => $user->id,
                'customer_id' => $saved->customer_id,
                'payment_method_id' => $saved->payment_method_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'nzd',
                'card_brand' => $paymentMethod->card->brand,
                'card_last4number' => $paymentMethod->card->last4,
                'status' => $intent->status,
            ]);

            Log::info('Payment processed', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'intent_id' => $intent->id,
                'status' => $intent->status,
            ]);

            return response()->json([
                'message' => 'Payment successful',
                'payment_intent' => $intent->id,
                'status' => $intent->status,
                'payment' => [
                    'id' => (string) $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'card_last4number' => $paymentMethod->card->last4,
                ]
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Card Error in chargeSavedCard', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Error charging saved card', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a payment method
     * Called by: DELETE /payments/{id}/delete
     */
    public function deletePayment(Request $request, $id)
    {
        $user = $request->user();

        $payment = Payment::where('id', $id)
            ->where('homeowner_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or you do not have access.',
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }

    /**
     * Update a payment record
     * Called by: PUT /payments/{id}/update
     */
    public function updatePayment(Request $request, $id)
    {
        $user = $request->user();

        $payment = Payment::where('id', $id)
            ->where('homeowner_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or you do not have access.',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3',
            'card_brand' => 'nullable|string|max:40',
            'card_last4number' => 'nullable|numeric|digits:4',
            'exp_month' => 'nullable|string|size:2',
            'exp_year' => 'nullable|string|size:4',
        ]);

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'data' => $payment,
        ]);
    }

    /**
     * View decrypted payment data
     * Called by: GET /payments/{id}/decrypt
     */
    public function viewDecryptedPayment(Request $request, $id)
    {
        $user = $request->user();

        $payment = Payment::where('id', $id)
            ->where('homeowner_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or you do not have access.',
            ], 404);
        }

        return response()->json([
            'message' => 'Decrypted data accessed successfully',
            'data' => $payment,
        ]);
    }
}