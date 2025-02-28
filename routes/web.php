<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MidtransController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('buses', BusController::class);
    Route::resource('cities', CityController::class);
    Route::resource('routes', RouteController::class);
    Route::resource('trips', TripController::class);
    Route::resource('transactions', TransactionController::class)->except(['edit', 'update']);
});

Route::post('/transactions/{transaction}', [TransactionController::class, 'update'])
    ->name('transactions.update');

Route::post('midtrans/notification', [MidtransController::class, 'handleNotification']);

require __DIR__.'/auth.php';
