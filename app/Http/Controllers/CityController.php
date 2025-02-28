<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CityRequest;
use App\Models\City;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CityController extends Controller
{
    public function index(): View
    {
        $cities = City::orderBy('name')->get();
        return view('cities.index', compact('cities'));
    }

    public function create(): View
    {
        return view('cities.form');
    }

    public function store(CityRequest $request): RedirectResponse
    {
        City::create($request->validated());
        
        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan');
    }

    public function edit(City $city): View
    {
        return view('cities.form', compact('city'));
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());
        
        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil diperbarui');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();
        
        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil dihapus');
    }
} 