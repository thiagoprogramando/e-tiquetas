<?php

use App\Http\Controllers\Access\ForgoutController;
use App\Http\Controllers\Access\LoginController;
use App\Http\Controllers\Access\RegisterController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Data\DataController;
use App\Http\Controllers\Label\ExportController;
use App\Http\Controllers\Label\LabelController;
use App\Http\Controllers\Layout\LayoutController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('logon', [LoginController::class, 'logon'])->name('logon');

Route::get('register/{indicator?}', [RegisterController::class, 'index'])->name('register');
Route::post('created-user', [RegisterController::class, 'store'])->name('created-user');

Route::get('/forgout/{code?}', [ForgoutController::class, 'index'])->name('forgout');
Route::post('/forgout-password', [ForgoutController::class, 'forgoutPassword'])->name('forgout-password');
Route::post('/recover-password/{code}', [ForgoutController::class, 'recoverPassword'])->name('recover-password');

Route::middleware(['auth'])->group(function () {

    Route::get('app', [AppController::class, 'index'])->name('app');

    Route::get('exports', [ExportController::class, 'index'])->name('exports');
    Route::post('deleted-export', [ExportController::class, 'destroy'])->name('deleted-export');

    Route::get('render', [LabelController::class, 'render'])->name('render');

    Route::get('layouts', [LayoutController::class, 'index'])->name('layouts');
    Route::get('layout/{uuid}', [LayoutController::class, 'show'])->name('layout');
    Route::post('created-layout', [LayoutController::class, 'store'])->name('created-layout');
    Route::post('deleted-layout', [LayoutController::class, 'destroy'])->name('deleted-layout');

    Route::get('data', [DataController::class, 'index'])->name('data');
    Route::get('create-data', [DataController::class, 'create'])->name('create-data');
    Route::post('process-data', [DataController::class, 'process'])->name('process-data');
    Route::post('created-data', [DataController::class, 'store'])->name('created-data');
    Route::post('updated-data', [DataController::class, 'update'])->name('updated-data');
    Route::post('deleted-data', [DataController::class, 'destroy'])->name('deleted-data');

    Route::get('user/{uuid}', [UserController::class, 'show'])->name('user');
    Route::post('created-account', [UserController::class, 'store'])->name('created-account');
    Route::post('updated-account/{uuid}', [UserController::class, 'update'])->name('updated-account');
    Route::post('deleted-account', [UserController::class, 'destroy'])->name('deleted-account');

    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
});