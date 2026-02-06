<?php

use App\Http\Controllers\Access\ForgoutController;
use App\Http\Controllers\Access\LoginController;
use App\Http\Controllers\Access\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('login', [LoginController::class, 'index'])->name('login');
Route::post('logon', [LoginController::class, 'logon'])->name('logon');

Route::get('register/{indicator?}', [RegisterController::class, 'index'])->name('register');
Route::post('created-user', [RegisterController::class, 'store'])->name('created-user');

Route::get('/forgout/{code?}', [ForgoutController::class, 'index'])->name('forgout');
Route::post('/forgout-password', [ForgoutController::class, 'forgoutPassword'])->name('forgout-password');
Route::post('/recover-password/{code}', [ForgoutController::class, 'recoverPassword'])->name('recover-password');