@extends('layouts.app')

@section('title', isset($trip) ? 'Edit Perjalanan' : 'Tambah Perjalanan')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
            {{ isset($trip) ? 'Edit Perjalanan' : 'Tambah Perjalanan Baru' }}
        </h2>

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ isset($trip) ? route('trips.update', $trip) : route('trips.store') }}" 
              method="POST" 
              class="space-y-6">
            @csrf
            @if(isset($trip))
                @method('PUT')
            @endif

            <div>
                <label for="bus_id" class="block text-sm font-medium text-gray-700">Bus</label>
                <select name="bus_id" 
                        id="bus_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Bus</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" 
                                {{ (old('bus_id', $trip->bus_id ?? '') == $bus->id) ? 'selected' : '' }}>
                            {{ $bus->name }} (Kapasitas: {{ $bus->capacity }} kursi)
                        </option>
                    @endforeach
                </select>
                @error('bus_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="route_id" class="block text-sm font-medium text-gray-700">Rute</label>
                <select name="route_id" 
                        id="route_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih Rute</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" 
                                {{ (old('route_id', $trip->route_id ?? '') == $route->id) ? 'selected' : '' }}>
                            {{ $route->fromCity->name }} → {{ $route->toCity->name }}
                        </option>
                    @endforeach
                </select>
                @error('route_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="departure_date" class="block text-sm font-medium text-gray-700">Tanggal Keberangkatan</label>
                <input type="date" 
                       name="departure_date" 
                       id="departure_date" 
                       min="{{ date('Y-m-d') }}"
                       value="{{ old('departure_date', isset($trip) ? $trip->departure_date->format('Y-m-d') : date('Y-m-d')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('departure_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('trips.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    {{ isset($trip) ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 