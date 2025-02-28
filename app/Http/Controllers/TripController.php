<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TripRequest;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TripController extends Controller
{
    public function index(): View
    {
        $trips = Trip::with(['bus', 'route.fromCity', 'route.toCity'])
            ->orderBy('departure_date')
            ->get();
        return view('trips.index', compact('trips'));
    }

    public function create(): View
    {
        $buses = Bus::orderBy('name')->get();
        $routes = Route::with(['fromCity', 'toCity'])->get();
        return view('trips.form', compact('buses', 'routes'));
    }

    public function store(TripRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $bus = Bus::findOrFail($validatedData['bus_id']);
            
            Trip::create([
                'bus_id' => $validatedData['bus_id'],
                'route_id' => $validatedData['route_id'],
                'departure_date' => $validatedData['departure_date'],
                'available_seats' => $bus->capacity
            ]);
            
            return redirect()
                ->route('trips.index')
                ->with('success', 'Perjalanan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan perjalanan: ' . $e->getMessage());
        }
    }

    public function edit(Trip $trip): View
    {
        $buses = Bus::orderBy('name')->get();
        $routes = Route::with(['fromCity', 'toCity'])->get();
        return view('trips.form', compact('trip', 'buses', 'routes'));
    }

    public function update(TripRequest $request, Trip $trip): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Update available_seats jika bus berubah
            if ($trip->bus_id !== (int) $validatedData['bus_id']) {
                $newBus = Bus::findOrFail($validatedData['bus_id']);
                $validatedData['available_seats'] = $newBus->capacity;
            }
            
            $trip->update($validatedData);
            
            return redirect()
                ->route('trips.index')
                ->with('success', 'Perjalanan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui perjalanan: ' . $e->getMessage());
        }
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        $trip->delete();
        
        return redirect()
            ->route('trips.index')
            ->with('success', 'Perjalanan berhasil dihapus');
    }
} 