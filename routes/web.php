<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('budgets', BudgetController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('transactions', TransactionController::class)->except('show');
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
