@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('content')
{{--
    View untuk form pembuatan transaksi baru
    
    Flow:
    1. Menampilkan form input
    2. Validasi input client-side
    3. Kalkulasi total harga otomatis
    4. Menampilkan pesan error jika ada
    
    Komponen:
    - Form dengan validasi
    - Select untuk pemilihan perjalanan
    - Input untuk data pelanggan
    - Kalkulasi harga real-time
    - Pesan error/success
--}}
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Tambah Transaksi Baru</h2>

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('transactions.store') }}" 
              method="POST" 
              class="space-y-6">
            @csrf

            <div>
                <label for="trip_id" class="block text-sm font-medium text-gray-700">Perjalanan</label>
                <select name="trip_id" 
                        id="trip_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Perjalanan</option>
                    @foreach($trips as $trip)
                        <option value="{{ $trip->id }}" 
                                {{ old('trip_id') == $trip->id ? 'selected' : '' }}
                                data-price="{{ $trip->route->price }}"
                                data-seats="{{ $trip->available_seats }}">
                            {{ $trip->route->fromCity->name }} → {{ $trip->route->toCity->name }}
                            ({{ $trip->departure_date->format('d/m/Y') }})
                            - {{ $trip->bus->name }}
                            - {{ $trip->available_seats }} kursi tersedia
                            - Rp {{ number_format($trip->route->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('trip_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                <input type="text" 
                       name="customer_name" 
                       id="customer_name" 
                       value="{{ old('customer_name') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('customer_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="customer_email" class="block text-sm font-medium text-gray-700">Email Pelanggan</label>
                <input type="email" 
                       name="customer_email" 
                       id="customer_email" 
                       value="{{ old('customer_email') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('customer_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="num_tickets" class="block text-sm font-medium text-gray-700">Jumlah Tiket</label>
                <input type="number" 
                       name="num_tickets" 
                       id="num_tickets" 
                       min="1"
                       value="{{ old('num_tickets') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('num_tickets')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Total Harga</h3>
                <p class="text-2xl font-bold text-gray-900" id="total_price">Rp 0</p>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('transactions.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const tripSelect = document.getElementById('trip_id');
    const numTicketsInput = document.getElementById('num_tickets');
    const totalPriceElement = document.getElementById('total_price');

    function updateTotalPrice() {
        const selectedOption = tripSelect.options[tripSelect.selectedIndex];
        const price = selectedOption ? parseFloat(selectedOption.dataset.price) : 0;
        const numTickets = parseInt(numTicketsInput.value) || 0;
        const total = price * numTickets;
        
        totalPriceElement.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }

    tripSelect.addEventListener('change', updateTotalPrice);
    numTicketsInput.addEventListener('input', updateTotalPrice);
</script>
@endpush
@endsection 