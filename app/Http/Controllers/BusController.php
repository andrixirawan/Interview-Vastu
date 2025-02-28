<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BusRequest;
use App\Models\Bus;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BusController extends Controller
{
    public function index(): View
    {
        $buses = Bus::orderBy('name')->get();
        return view('buses.index', compact('buses'));
    }

    public function create(): View
    {
        return view('buses.form');
    }

    public function store(BusRequest $request): RedirectResponse
    {
        Bus::create($request->validated());
        
        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus berhasil ditambahkan');
    }

    public function edit(Bus $bus): View
    {
        return view('buses.form', compact('bus'));
    }

    public function update(BusRequest $request, Bus $bus): RedirectResponse
    {
        $bus->update($request->validated());
        
        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus berhasil diperbarui');
    }

    public function destroy(Bus $bus): RedirectResponse
    {
        $bus->delete();
        
        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus berhasil dihapus');
    }
} 