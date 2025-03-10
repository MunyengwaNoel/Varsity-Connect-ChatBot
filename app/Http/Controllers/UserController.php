<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public static function welcome(){
        return "Welcome to RentOra Housing.\n1. Login\n2. Register";
    }
    public static function register(Request $request):array{
        $data = ["message"=>"Enter Your      Name:",
            "next"=> 
        ]
    }

    public static function login(){
        return "Enter Password";
    }



}
