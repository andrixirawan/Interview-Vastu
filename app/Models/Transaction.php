<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Midtrans\Snap;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'customer_name',
        'customer_email',
        'num_tickets',
        'total_price',
        'payment_status',
        'snap_token',
        'payment_url',
        'payment_type',
        'payment_time',
        'transaction_id',
        'transaction_status',
        'fraud_status',
        'payment_details'
    ];

    protected $casts = [
        'payment_time' => 'datetime',
        'payment_details' => 'array',
        'total_price' => 'decimal:2'
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function generatePaymentToken(): void
    {
        try {
            if (!config('midtrans.server_key')) {
                throw new \Exception('Midtrans server key tidak ditemukan');
            }

            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized', true);
            \Midtrans\Config::$is3ds = config('midtrans.is_3ds', true);

            $params = [
                'transaction_details' => [
                    'order_id' => 'TRX-' . $this->id,
                    'gross_amount' => (int) $this->total_price,
                ],
                'customer_details' => [
                    'first_name' => $this->customer_name,
                    'email' => $this->customer_email,
                ],
                'item_details' => [
                    [
                        'id' => $this->trip_id,
                        'price' => (int) $this->trip->route->price,
                        'quantity' => $this->num_tickets,
                        'name' => sprintf(
                            'Tiket Bus %s (%s → %s)',
                            $this->trip->bus->name,
                            $this->trip->route->fromCity->name,
                            $this->trip->route->toCity->name
                        ),
                    ]
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $this->update([
                'snap_token' => $snapToken,
                'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken,
                'transaction_id' => 'TRX-' . $this->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in generatePaymentToken:', [
                'transaction_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update status pembayaran berdasarkan callback Midtrans
     * 
     * Flow:
     * 1. Menerima data callback dari Midtrans
     * 2. Update status dan detail pembayaran
     * 3. Jika sukses, status menjadi 'paid'
     * 4. Jika gagal, status menjadi 'failed' dan kembalikan kursi
     * 
     * Status yang mungkin:
     * - capture: Pembayaran kartu kredit berhasil
     * - settlement: Pembayaran berhasil
     * - pending: Menunggu pembayaran
     * - deny: Pembayaran ditolak
     * - cancel: Pembayaran dibatalkan
     * - expire: Pembayaran expired
     * - refund: Pembayaran dikembalikan
     */
    public function handlePaymentNotification(array $payload): void
    {
        // Pastikan semua field yang diperlukan ada
        $payload = array_merge([
            'payment_type' => 'unknown',
            'transaction_time' => now(),
            'transaction_status' => 'pending',
            'fraud_status' => null,
            'payment_details' => []
        ], $payload);

        // Update data pembayaran
        $this->update([
            'payment_type' => $payload['payment_type'],
            'payment_time' => $payload['transaction_time'],
            'transaction_status' => $payload['transaction_status'],
            'fraud_status' => $payload['fraud_status'],
            'payment_details' => $payload
        ]);

        // Handle status pembayaran
        switch ($payload['transaction_status']) {
            case 'capture':
            case 'settlement':
                $this->markAsPaid();
                break;
            case 'pending':
                $this->markAsPending();
                break;
            case 'deny':
            case 'cancel':
            case 'expire':
                $this->markAsFailed();
                break;
            case 'refund':
                $this->markAsRefunded();
                break;
        }
    }

    /**
     * Tandai transaksi sebagai sudah dibayar
     */
    private function markAsPaid(): void
    {
        $this->update(['payment_status' => 'paid']);
    }

    /**
     * Tandai transaksi sebagai pending
     */
    private function markAsPending(): void
    {
        $this->update(['payment_status' => 'pending']);
    }

    /**
     * Tandai transaksi sebagai gagal dan kembalikan kursi
     */
    private function markAsFailed(): void
    {
        // Kembalikan kursi yang sudah dipesan
        $this->trip->increment('available_seats', $this->num_tickets);
        $this->update(['payment_status' => 'failed']);
    }

    /**
     * Tandai transaksi sebagai refund dan kembalikan kursi
     */
    private function markAsRefunded(): void
    {
        // Kembalikan kursi yang sudah dipesan
        $this->trip->increment('available_seats', $this->num_tickets);
        $this->update(['payment_status' => 'refunded']);
    }
} 