<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController;

Route::get('get-session', function (Request $request) {
    dd($request->session()->all());
});

Route::prefix('')->group(function () {

    #Home
    Route::prefix('')->name('')->group(function () {

        #Index
        Route::get('', [HomeController::class, 'index'])->name('home');

        Route::get('thanh-toan', [OrderController::class, 'index'])->middleware(['checkout', 'auth'])->name('checkout');
        Route::post('thanh-toan', [OrderController::class, 'checkout'])->middleware(['checkout', 'auth']);

        Route::get('vnpay-return', [TransactionController::class, 'vnpayReturn'])->middleware('auth')->name('vnpay.return');

        #Login
        Route::get('dang-nhap', [HomeController::class, 'login'])->name('login');
        Route::post('dang-nhap', [HomeController::class, 'handleLogin']);

        #Register
        Route::get('dang-ky', [HomeController::class, 'register'])->name('register');
        Route::post('dang-ky', [HomeController::class, 'handleRegister']);

        #Logout
        Route::get('logout', [HomeController::class, 'logout'])->name('logout');
    });

    Route::get('gio-hang', [CartController::class, 'index'])->name('cart')->middleware('auth');
});
