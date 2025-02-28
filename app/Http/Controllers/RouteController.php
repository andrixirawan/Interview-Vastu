<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RouteRequest;
use App\Models\City;
use App\Models\Route;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RouteController extends Controller
{
    public function index(): View
    {
        $routes = Route::with(['fromCity', 'toCity'])->get();
        return view('routes.index', compact('routes'));
    }

    public function create(): View
    {
        $cities = City::orderBy('name')->get();
        return view('routes.form', compact('cities'));
    }

    public function store(RouteRequest $request): RedirectResponse
    {
        Route::create($request->validated());
        
        return redirect()
            ->route('routes.index')
            ->with('success', 'Rute berhasil ditambahkan');
    }

    public function edit(Route $route): View
    {
        $cities = City::orderBy('name')->get();
        return view('routes.form', compact('route', 'cities'));
    }

    public function update(RouteRequest $request, Route $route): RedirectResponse
    {
        $route->update($request->validated());
        
        return redirect()
            ->route('routes.index')
            ->with('success', 'Rute berhasil diperbarui');
    }

    public function destroy(Route $route): RedirectResponse
    {
        $route->delete();
        
        return redirect()
            ->route('routes.index')
            ->with('success', 'Rute berhasil dihapus');
    }
} 