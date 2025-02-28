@extends('layouts.app')

@section('title', 'Daftar Transaksi')

@section('content')
{{-- 
    View untuk menampilkan daftar transaksi
    
    Flow:
    1. Menampilkan header dengan tombol tambah transaksi
    2. Menampilkan tabel daftar transaksi
    3. Untuk setiap transaksi:
        - Menampilkan informasi dasar transaksi
        - Menampilkan status pembayaran dengan warna yang sesuai
        - Menampilkan tombol aksi (Detail, Bayar, Batalkan)
    4. Menampilkan pesan jika tidak ada transaksi
    5. Mengintegrasikan Midtrans untuk pembayaran
    
    Komponen:
    - Tabel responsive
    - Badge status dengan warna dinamis
    - Tombol aksi dengan konfirmasi
    - Modal pembayaran Midtrans
--}}
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Daftar Transaksi</h2>
            <a href="{{ route('transactions.create') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Tambah Transaksi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Tiket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->id }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $transaction->customer_name }}</div>
                            <div class="text-sm text-gray-500">{{ $transaction->customer_email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $transaction->trip->route->fromCity->name }} → {{ $transaction->trip->route->toCity->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $transaction->trip->departure_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $transaction->num_tickets }} tiket
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $transaction->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($transaction->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($transaction->payment_status) }}
                            </span>
                            @if($transaction->payment_type)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ ucfirst($transaction->payment_type) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="{{ route('transactions.show', $transaction) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                @if($transaction->payment_status === 'pending')
                                    <!-- Debug info -->
                                    <div class="text-xs text-gray-500">
                                        Debug: Token: {{ $transaction->snap_token ?? 'No token' }}
                                    </div>
                                    <button class="pay-button text-green-600 hover:text-green-900"
                                            data-token="{{ $transaction->snap_token }}">
                                        Bayar
                                    </button>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                            Tidak ada data transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
{{-- 
    Integrasi Midtrans di halaman index
    
    Flow:
    1. Load Snap.js Midtrans
    2. Inisialisasi tombol pembayaran
    3. Handle klik tombol pembayaran
    4. Tampilkan popup Midtrans
    5. Handle callback pembayaran
    
    Debugging:
    - Log client key
    - Log snap token
    - Log status pembayaran
    
    Error Handling:
    - Cek ketersediaan token
    - Cek Snap.js loaded
    - Alert untuk error
--}}

<!-- Load Midtrans Snap.js -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<!-- Inisialisasi pembayaran -->
<script>
$(document).ready(function() {
    // Debug: Cek client key
    console.log('Midtrans Client Key:', '{{ config('midtrans.client_key') }}');
    
    // Handle klik tombol pembayaran
    $('.pay-button').on('click', function(e) {
        e.preventDefault();
        const snapToken = $(this).data('token');
        
        // Debug: Cek token
        console.log('Button clicked, token:', snapToken);
        
        // Validasi token
        if (!snapToken) {
            console.error('No snap token found');
            alert('Token pembayaran tidak ditemukan');
            return;
        }
        
        // Cek Snap.js
        if (typeof window.snap !== 'undefined') {
            console.log('Initializing payment with token:', snapToken);
            
            // Mulai pembayaran
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.reload();
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    window.location.reload();
                },
                onError: function(result) {
                    console.error('Payment error:', result);
                    alert('Pembayaran gagal');
                    window.location.reload();
                },
                onClose: function() {
                    console.log('Payment widget closed');
                }
            });
        } else {
            console.error('Snap.js not loaded properly');
            alert('Terjadi kesalahan saat memuat sistem pembayaran');
        }
    });
});
</script>
@endpush
@endsection 