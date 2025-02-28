@extends('layouts.app')

@section('title', isset($route) ? 'Edit Rute' : 'Tambah Rute')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
            {{ isset($route) ? 'Edit Rute' : 'Tambah Rute Baru' }}
        </h2>

        <form action="{{ isset($route) ? route('routes.update', $route) : route('routes.store') }}" 
              method="POST" 
              class="space-y-6">
            @csrf
            @if(isset($route))
                @method('PUT')
            @endif

            <div>
                <label for="from_city_id" class="block text-sm font-medium text-gray-700">Kota Asal</label>
                <select name="from_city_id" 
                        id="from_city_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Kota Asal</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" 
                                {{ (old('from_city_id', $route->from_city_id ?? '') == $city->id) ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
                @error('from_city_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="to_city_id" class="block text-sm font-medium text-gray-700">Kota Tujuan</label>
                <select name="to_city_id" 
                        id="to_city_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Kota Tujuan</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" 
                                {{ (old('to_city_id', $route->to_city_id ?? '') == $city->id) ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
                @error('to_city_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Harga Tiket</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           min="0"
                           step="0.01"
                           value="{{ old('price', $route->price ?? '') }}"
                           class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('routes.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    {{ isset($route) ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 