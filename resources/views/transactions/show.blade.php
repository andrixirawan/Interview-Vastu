@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
{{--
    View untuk menampilkan detail transaksi
    
    Flow:
    1. Menampilkan header dengan ID transaksi
    2. Menampilkan informasi pelanggan
    3. Menampilkan detail perjalanan
    4. Menampilkan informasi pembayaran
    5. Menampilkan status dan riwayat pembayaran
    6. Menampilkan tombol pembayaran jika status pending
    
    Komponen:
    - Grid layout untuk informasi
    - Status badge
    - Tombol pembayaran Midtrans
    - Riwayat pembayaran jika ada
--}}
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                Detail Transaksi #{{ $transaction->id }}
                <div class="text-sm text-gray-500 font-normal">
                    Order ID: {{ $transaction->transaction_id }}
                </div>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('transactions.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pelanggan</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer_email }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Perjalanan</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rute</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $transaction->trip->route->fromCity->name }} → {{ $transaction->trip->route->toCity->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Bus</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $transaction->trip->bus->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Keberangkatan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $transaction->trip->departure_date->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pembayaran</h3>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Harga per Tiket</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            Rp {{ number_format($transaction->trip->route->price, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jumlah Tiket</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $transaction->num_tickets }} tiket</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Harga</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">
                            Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                @if($transaction->payment_status === 'pending')
                    {{-- 
                        Komponen pembayaran Midtrans
                        
                        Flow:
                        1. Menampilkan tombol pembayaran
                        2. Saat diklik, memunculkan popup Midtrans
                        3. Menangani callback dari proses pembayaran
                        4. Merefresh halaman sesuai status pembayaran
                        
                        Integrasi:
                        - Menggunakan Snap.js dari Midtrans
                        - Menggunakan client key dari config
                        - Menggunakan snap token yang tersimpan
                        
                        Callback:
                        - onSuccess: Pembayaran berhasil
                        - onPending: Pembayaran menunggu
                        - onError: Pembayaran gagal
                        - onClose: Popup ditutup
                    --}}
                    <div class="mt-6">
                        <button id="pay-button" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg">
                            Bayar Sekarang
                        </button>
                    </div>

                    @push('scripts')
                    {{-- Load Midtrans Snap.js --}}
                    <script src="https://app.sandbox.midtrans.com/snap/snap.js" 
                            data-client-key="{{ config('midtrans.client_key') }}"></script>
                    
                    {{-- Inisialisasi pembayaran Midtrans --}}
                    <script>
                        const payButton = document.getElementById('pay-button');
                        payButton.addEventListener('click', function () {
                            window.snap.pay('{{ $transaction->snap_token }}', {
                                onSuccess: function(result) {
                                    // Kirim data ke server untuk update status
                                    fetch('{{ route('transactions.update', $transaction) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            status: 'success',
                                            result: result
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            window.location.reload();
                                        } else {
                                            alert(data.message || 'Terjadi kesalahan saat memperbarui status');
                                            window.location.reload();
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Terjadi kesalahan saat memperbarui status');
                                        window.location.reload();
                                    });
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
                        });
                    </script>
                    @endpush
                @else
                    {{-- Menampilkan informasi pembayaran yang sudah selesai --}}
                    <div class="mt-4 p-4 rounded-lg {{ $transaction->payment_status === 'paid' ? 'bg-green-100' : 'bg-red-100' }}">
                        <p class="font-medium {{ $transaction->payment_status === 'paid' ? 'text-green-800' : 'text-red-800' }}">
                            Status Pembayaran: {{ ucfirst($transaction->payment_status) }}
                        </p>
                        @if($transaction->payment_type)
                            <p class="text-sm mt-1">Metode Pembayaran: {{ ucfirst($transaction->payment_type) }}</p>
                        @endif
                        @if($transaction->payment_time)
                            <p class="text-sm mt-1">Waktu Pembayaran: {{ $transaction->payment_time->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 