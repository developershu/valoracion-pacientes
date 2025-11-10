<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ValoracionController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [TurnoController::class, 'index'])->name('turnos.index');
    Route::get('/turnos', [TurnoController::class, 'index']);
    Route::get('/turnos/{id}', [TurnoController::class, 'show'])->name('turnos.show');
    Route::post('/turnos/{id}/valorar', [ValoracionController::class, 'store'])->name('turnos.valorar');
});
