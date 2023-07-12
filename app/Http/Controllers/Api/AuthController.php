<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Handle an incoming login request.
     *
     * @param  LoginUserRequest  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
       // $credentials = ['email' => $request->email, 'password' => $request->password];
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->error('', 'Credentials do not match.', 401);
        }

        $user = User::where('email', $request->email)->first();

        //$token = $user->createToken('API Token of ' . $user->name);
        $token = $user->createToken('API Token of ' . $user->name)->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], 'You have been successfully logged in.');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  StoreUserRequest  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token of ' . $request->name)->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], 'You have been successfully registered.');
    }

    /**
     * Handle an incoming logout request.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success('', 'You have successfully been logged out', 200);
     }
}
