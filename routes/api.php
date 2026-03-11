<?php

use App\Http\Controllers\Layout\LayoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('updated-layout', [LayoutController::class, 'update'])->name('updated-layout');