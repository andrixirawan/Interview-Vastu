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
        $payload = $request->all();
        
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        
        // Generate signature key untuk validasi
        $validSignatureKey = hash('sha512', 
            $orderId . $statusCode . $grossAmount . config('midtrans.server_key')
        );

        // Validasi signature key
        if ($payload['signature_key'] !== $validSignatureKey) {
            return response('Invalid signature', 400);
        }

        // Cari dan update transaksi
        $transaction = Transaction::where('transaction_id', $orderId)->firstOrFail();
        $transaction->handlePaymentNotification($payload);
        
        return response('OK', 200);
    }
} 