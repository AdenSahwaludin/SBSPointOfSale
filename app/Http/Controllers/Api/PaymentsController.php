<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentsController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Create Snap token for payment
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:1',
            'customer.name' => 'required|string|max:50',
            'customer.email' => 'required|email',
            'customer.phone' => 'required|string|max:20',
            'items' => 'array',
            'items.*.id' => 'required|string',
            'items.*.price' => 'required|integer|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Generate unique order ID
            $orderId = 'SBS-' . date('Ymd') . '-' . Str::random(8);
            
            // Ensure order ID is unique
            while (Order::where('order_id', $orderId)->exists()) {
                $orderId = 'SBS-' . date('Ymd') . '-' . Str::random(8);
            }

            // Prepare payment parameters
            $amount = $request->amount;
            $customer = $request->customer;
            $items = $request->items ?? [];

            $paymentParams = $this->midtrans->buildPaymentParams($orderId, $amount, $customer, $items);

            // Create Snap token
            $snapResponse = $this->midtrans->createSnapToken($paymentParams);

            // Save order to database
            $order = Order::create([
                'order_id' => $orderId,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'amount' => $amount,
                'status' => 'pending',
                'snap_token' => $snapResponse['token'],
                'snap_redirect_url' => $snapResponse['redirect_url'] ?? null,
                'meta' => [
                    'items' => $items,
                    'created_by' => 'pos_system',
                ],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'snap_token' => $snapResponse['token'],
                    'redirect_url' => $snapResponse['redirect_url'] ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification webhook
     */
    public function notification(Request $request)
    {
        try {
            $notification = $request->all();
            
            // Log the notification
            Log::info('Midtrans notification received', $notification);

            // Verify signature
            if (!$this->midtrans->verifySignature($notification)) {
                Log::warning('Invalid Midtrans signature', $notification);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;
            $paymentType = $notification['payment_type'] ?? null;

            if (!$orderId || !$transactionStatus) {
                return response()->json(['message' => 'Invalid notification data'], 400);
            }

            // Find order
            $order = Order::where('order_id', $orderId)->first();
            if (!$order) {
                Log::warning('Order not found for notification', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Log payment notification
            PaymentLog::create([
                'order_id' => $orderId,
                'raw_payload' => $notification,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
            ]);

            // Update order status based on transaction status
            $newStatus = $this->midtrans->mapTransactionStatus($transactionStatus, $fraudStatus);
            
            DB::transaction(function () use ($order, $newStatus, $paymentType) {
                if ($newStatus === 'paid' && !$order->isPaid()) {
                    $order->markAsPaid($paymentType);
                } elseif ($newStatus === 'failed' && $order->isPending()) {
                    $order->markAsFailed();
                } else {
                    // Update status for other cases (pending, etc.)
                    $order->update(['status' => $newStatus]);
                }
            });

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Midtrans notification processing failed', [
                'error' => $e->getMessage(),
                'notification' => $request->all(),
            ]);

            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
