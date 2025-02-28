<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\Trip;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi
     * 
     * Flow:
     * 1. Mengambil semua transaksi dari database
     * 2. Eager loading relasi trip, route, dan city untuk optimasi query
     * 3. Mengurutkan berdasarkan created_at terbaru
     * 4. Menampilkan view dengan data transaksi
     */
    public function index(): View
    {
        $transactions = Transaction::with(['trip.route.fromCity', 'trip.route.toCity'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('transactions.index', compact('transactions'));
    }

    /**
     * Menampilkan form pembuatan transaksi baru
     * 
     * Flow:
     * 1. Mengambil daftar perjalanan yang tersedia
     * 2. Hanya menampilkan perjalanan dengan tanggal keberangkatan >= hari ini
     * 3. Hanya menampilkan perjalanan yang masih memiliki kursi kosong
     * 4. Mengurutkan berdasarkan tanggal keberangkatan
     */
    public function create(): View
    {
        $trips = Trip::with(['route.fromCity', 'route.toCity'])
            ->where('departure_date', '>=', now())
            ->where('available_seats', '>', 0)
            ->orderBy('departure_date')
            ->get();
        return view('transactions.form', compact('trips'));
    }

    /**
     * Menyimpan transaksi baru
     * 
     * Flow:
     * 1. Memulai transaksi database
     * 2. Validasi ketersediaan kursi
     * 3. Membuat record transaksi baru
     * 4. Generate token pembayaran Midtrans
     * 5. Update jumlah kursi tersedia
     * 6. Commit transaksi database
     * 7. Redirect ke halaman detail transaksi
     * 
     * Error Handling:
     * - Validasi jumlah kursi
     * - Rollback jika terjadi error
     * - Mengembalikan pesan error ke user
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $trip = Trip::findOrFail($request->validated()['trip_id']);
            
            if ($request->validated()['num_tickets'] > $trip->available_seats) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jumlah tiket melebihi kursi yang tersedia');
            }
            
            $transaction = Transaction::create([
                'trip_id' => $trip->id,
                'customer_name' => $request->validated()['customer_name'],
                'customer_email' => $request->validated()['customer_email'],
                'num_tickets' => $request->validated()['num_tickets'],
                'total_price' => $trip->route->price * $request->validated()['num_tickets'],
                'payment_status' => 'pending'
            ]);
            
            // Debug log sebelum generate token
            \Log::info('Creating transaction:', [
                'id' => $transaction->id,
                'total_price' => $transaction->total_price
            ]);
            
            // Generate Midtrans payment token
            $transaction->generatePaymentToken();
            
            // Debug log setelah generate token
            \Log::info('Payment token generated:', [
                'id' => $transaction->id,
                'transaction_id' => $transaction->transaction_id,
                'snap_token' => $transaction->snap_token
            ]);
            
            // Update available seats
            $trip->decrement('available_seats', $request->validated()['num_tickets']);
            
            DB::commit();
            
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil dibuat. Silakan lakukan pembayaran.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail transaksi
     * 
     * Flow:
     * 1. Load transaksi berdasarkan ID
     * 2. Menampilkan informasi detail transaksi
     * 3. Menampilkan status pembayaran
     * 4. Menampilkan tombol pembayaran jika status masih pending
     */
    public function show(Transaction $transaction): View
    {
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Membatalkan transaksi
     * 
     * Flow:
     * 1. Mengembalikan jumlah kursi yang dibatalkan
     * 2. Menghapus record transaksi
     * 3. Redirect ke halaman daftar transaksi
     * 
     * Error Handling:
     * - Menangani kegagalan pembatalan
     * - Mengembalikan pesan error ke user
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        try {
            // Restore available seats
            $transaction->trip->increment('available_seats', $transaction->num_tickets);
            
            $transaction->delete();
            
            return redirect()
                ->route('transactions.index')
                ->with('success', 'Transaksi berhasil dibatalkan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Update status transaksi dari callback frontend
     */
    public function update(Request $request, Transaction $transaction)
    {
        try {
            if ($request->status === 'success') {
                $transaction->handlePaymentNotification([
                    'transaction_status' => 'settlement',
                    'payment_type' => $request->result['payment_type'] ?? 'unknown',
                    'transaction_time' => now(),
                    'fraud_status' => null,
                    'payment_details' => $request->result
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Status pembayaran tidak valid'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Error updating transaction:', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status transaksi'
            ], 500);
        }
    }
} 