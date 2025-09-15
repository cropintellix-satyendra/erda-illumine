<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Organization\Auth\LoginController;
use App\Http\Controllers\Organization\DashboardController;
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/





Route::get('/', [LoginController::class, 'index']);
// Route::get('/login', [LoginController::class, 'index'])->name('login');
// Route::post('/signin', [LoginController::class, 'login']);

Route::middleware(['web','auth'])->group( function(){


    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard.index');
    Route::get('fetch/dashboard/counting', [DashboardController::class, 'counting']);
    Route::get('fetch/all-farmer/counting', [DashboardController::class, 'all_farmers_counting']);

});
