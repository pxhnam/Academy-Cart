<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TransactionController;


Route::prefix('')->group(function () {

    #Index
    Route::get('', [HomeController::class, 'index'])->name('home');

    #Login
    Route::get('dang-nhap', [HomeController::class, 'login'])->name('login');
    Route::post('dang-nhap', [HomeController::class, 'handleLogin']);

    #Register
    Route::get('dang-ky', [HomeController::class, 'register'])->name('register');
    Route::post('dang-ky', [HomeController::class, 'handleRegister']);

    #Logout
    Route::get('logout', [HomeController::class, 'logout'])->name('logout');

    #Auth
    Route::prefix('')
        ->middleware('auth')
        ->group(function () {

            #Carts
            Route::get('gio-hang', [CartController::class, 'index'])->name('cart');

            #Payment
            Route::get('thanh-toan', [OrderController::class, 'index'])
                ->middleware('checkout')
                ->name('checkout');
            Route::post('thanh-toan', [OrderController::class, 'checkout'])
                ->middleware('checkout');

            #Result payment
            Route::get('ket-qua', [OrderController::class, 'result'])
                ->name('result');

            #Response payment
            Route::get('vnpay-return', [TransactionController::class, 'vnpayReturn'])
                ->name('vnpay.return');
            Route::get('momo-return', [TransactionController::class, 'momoReturn'])
                ->name('momo.return');
        });
});

//API
Route::prefix('carts')
    ->middleware('authenticate')
    ->name('carts.')
    ->group(function () {
        Route::get('', [CartController::class, 'list'])->name('list');
        Route::get('summary', [CartController::class, 'summary'])->name('summary');
        Route::post('add', [CartController::class, 'addToCart'])->name('add');
        Route::post('remove', [CartController::class, 'remove'])->name('remove');
        Route::post('checkout', [CartController::class, 'checkout'])->name('checkout');
        Route::get('recommend', [CartController::class, 'recommend'])->name('recommend');
    });

Route::prefix('courses')->group(function () {
    Route::get('', [CourseController::class, 'get'])->name('courses');
});
