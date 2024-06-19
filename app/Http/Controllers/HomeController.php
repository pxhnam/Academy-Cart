<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
        return view("client.home.index");
    }

    public function login()
    {
        return view("client.home.login");
    }

    public function register()
    {
        return view("client.home.register");
    }


    public function handleLogin(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ],
            [
                'required' => ':attribute không hợp lệ.',
                'email' => ':attribute không hợp lệ.',
            ],
            [
                'email' => 'Email',
                'password' => 'Mật khẩu'
            ]
        );

        if ($validator->passes()) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->intended('/');
            } else {
                return back()->onlyInput('email')->with('error', 'Tài khoản hoặc mật khẩu không chính xác');
            }
        } else {
            return back()->onlyInput('email')->withErrors($validator);
        }
    }

    public function handleRegister(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed'
            ],
            [
                'required' => ':attribute không hợp lệ.',
                'email' => ':attribute không hợp lệ.',
                'unique' => ':attribute đã được sử dụng.',
                'confirmed' => ':attribute không khớp.',
            ],
            [
                'name' => 'Tên',
                'email' => 'Email',
                'password' => 'Mật khẩu'
            ]
        );

        if ($validator->passes()) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            return redirect()->route('login');
        } else {
            return back()->onlyInput('name', 'email')->withErrors($validator);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }


    public function sendEmail($view, $params, $subject, $to, $name)
    {
        Mail::send($view, $params, function ($email) use ($subject, $to, $name) {
            $email->subject($subject);
            $email->to($to, $name);
        });
    }
}
