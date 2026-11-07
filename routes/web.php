<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\ClaimTicketController;
use App\Http\Controllers\Api\GameStateController;
use App\Http\Controllers\Api\PlayController;
use App\Http\Controllers\Api\RefillController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LobbyController::class, 'index'])->name('lobby');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/credit', [AdminController::class, 'credit'])->name('admin.credit');
});

Route::middleware('auth')->group(function () {
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/tickets/{code}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/api/games/{game}', [GameStateController::class, 'show'])->name('games.state');
    Route::post('/api/games/{game}/play', PlayController::class)->name('games.play');
    Route::post('/api/tickets/claim', ClaimTicketController::class)->name('tickets.claim');
    Route::post('/api/wallet/refill', RefillController::class)->name('wallet.refill');
});
