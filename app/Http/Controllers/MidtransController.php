<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller untuk menangani webhook Midtrans
 * 
 * Flow:
 * 1. Menerima notifikasi dari Midtrans
 * 2. Memvalidasi signature key
 * 3. Mencari transaksi berdasarkan order ID
 * 4. Mengupdate status pembayaran
 * 5. Mengembalikan response ke Midtrans
 * 
 * Security:
 * - Validasi signature key untuk memastikan request dari Midtrans
 * - Menggunakan server key yang aman
 * - Memastikan integritas data
 */
class MidtransController extends Controller
{
    /**
     * Menangani notifikasi webhook dari Midtrans
     * 
     * Flow:
     * 1. Mengambil data dari request Midtrans
     * 2. Mengekstrak order_id, status_code, dan gross_amount
     * 3. Memvalidasi signature key untuk keamanan
     * 4. Mencari transaksi berdasarkan order_id
     * 5. Mengupdate status transaksi
     * 6. Mengembalikan response ke Midtrans
     * 
     * Security Check:
     * - Memvalidasi signature key dengan hash SHA-512
     * - Memastikan request berasal dari Midtrans
     * - Memastikan data transaksi valid
     * 
     * Error Handling:
     * - Invalid signature: return 400
     * - Transaksi tidak ditemukan: throw 404
     * - Error lainnya: throw exception
     */
    public function handleNotification(Request $request): Response
    {
        try {
            $notificationBody = $request->getContent();
            $notification = json_decode($notificationBody, true);

            // Cari transaksi berdasarkan transaction_id yang baru
            $transaction = Transaction::where('transaction_id', $notification['order_id'])->firstOrFail();

            $validSignatureKey = hash('sha512', 
                $notification['order_id'] . 
                $notification['status_code'] . 
                $notification['gross_amount'] . 
                config('midtrans.server_key')
            );

            if ($notification['signature_key'] !== $validSignatureKey) {
                return response('Invalid signature', 400);
            }

            $transaction->handlePaymentNotification($notification);

            return response('OK', 200);
        } catch (\Exception $e) {
            \Log::error('Midtrans notification error:', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response('Error processing notification', 500);
        }
    }
} 