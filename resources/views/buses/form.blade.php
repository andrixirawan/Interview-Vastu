@extends('layouts.app')

@section('title', isset($bus) ? 'Edit Bus' : 'Tambah Bus')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
            {{ isset($bus) ? 'Edit Bus' : 'Tambah Bus Baru' }}
        </h2>

        <form action="{{ isset($bus) ? route('buses.update', $bus) : route('buses.store') }}" 
              method="POST" 
              class="space-y-6">
            @csrf
            @if(isset($bus))
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Bus</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $bus->name ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700">Kapasitas</label>
                <input type="number" 
                       name="capacity" 
                       id="capacity" 
                       min="1"
                       value="{{ old('capacity', $bus->capacity ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('capacity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('buses.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    {{ isset($bus) ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 