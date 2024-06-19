<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private $cartService;
    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }
    public function index()
    {
        return view("client.home.cart");
    }
    public function list()
    {
        return $this->cartService->list();
    }
    public function addToCart(Request $request)
    {
        $course_id = $request->id;
        return $this->cartService->add($course_id);
    }
    public function summary(Request $request)
    {
        return $this->cartService->summary($request);
    }
    public function checkout(Request $request)
    {
        return $this->cartService->checkout($request);
    }
    public function remove(Request $request)
    {
        $cart_id = $request->id;
        return $this->cartService->remove($cart_id);
    }
    public function recommend()
    {
        return $this->cartService->listRecommend();
    }
}
