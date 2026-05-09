<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('dashboard');
Route::view('/transactions', 'pages.transactions')->name('transactions');
Route::view('/budgets', 'pages.budgets')->name('budgets');
Route::view('/reports', 'pages.reports')->name('reports');
Route::view('/settings', 'pages.settings')->name('settings');
