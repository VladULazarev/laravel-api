<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Handle an incoming login request.
     *
     * @param  App\Http\Requests\LoginUserRequest  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(LoginUserRequest $request)
    {
        $credentials = ['email' => $request->email, 'password' => $request->password];

        if (! Auth::attempt($credentials)) {
            return $this->error('', 'Credentials do not match.', 401);
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('API Token of ' . $user->name);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken
        ], 'You have been successfully logged in.');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token of ' . $request->name);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken
        ], 'You have been successfully registered.');
    }

    /**
     * Handle an incoming logout request.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success('', 'You have successfully been logged out', 200);
     }
}
