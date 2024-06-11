<?php

namespace App\Providers;

use App\Models\Cart;
use App\Policies\CartPolicy;
use App\Services\CartService;
use App\Services\VNPayService;
use App\Services\CouponService;
use App\Services\CourseService;
use App\Repositories\CartRepository;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;
use App\Repositories\CouponRepository;
use App\Repositories\CourseRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\TransactionRepository;
use App\Services\Interfaces\CartServiceInterface;
use App\Services\Interfaces\VNPayServiceInterface;
use App\Services\Interfaces\CouponServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\OrderDetailRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\OrderDetailRepository;
use App\Repositories\OrderRepository;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService;

class AppServiceProvider extends ServiceProvider
{

    // protected $policies = [
    //     Cart::class => CartPolicy::class,
    // ];
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // $this->registerPolicies();

        # inject courses
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(CourseServiceInterface::class, CourseService::class);

        # inject carts
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CartServiceInterface::class, CartService::class);

        # inject transaction
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);

        # inject coupons
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(CouponServiceInterface::class, CouponService::class);

        # inject orders
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);

        # inject order_details
        $this->app->bind(OrderDetailRepositoryInterface::class, OrderDetailRepository::class);

        # inject vnpay
        $this->app->bind(VNPayServiceInterface::class, VNPayService::class);


        #Policies
        Gate::policy(Cart::class, CartPolicy::class);
    }
}
