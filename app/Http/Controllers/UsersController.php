<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    private function getToken()
    {
        Auth::user()->tokens()->delete();
        return Auth::user()->createToken('browser')->plainTextToken;
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return $this->getToken();
    }

    public function register(RegisterUserRequest $request)
    {
        $user = User::create($request->validated());
        Auth::login($user);
        return $this->getToken();
    }
}
