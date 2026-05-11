<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/', 'pages.dashboard')->name('dashboard');
    Route::resource('budgets', BudgetController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::view('/transactions', 'pages.transactions')->name('transactions');
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
