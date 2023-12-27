<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // ... Other methods in the LoginController

    protected function attemptLogin(array $credentials, $remember)
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            Log::warning('Login attempt failed due to empty email or password.');
            return false;
        }

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            Log::warning('Login attempt failed for email: ' . $credentials['email']);
            return false;
        }

        $this->handleUserSession($user, $remember);
        Auth::login($user, $remember);
        return true;
    }

    protected function handleUserSession(User $user, $remember)
    {
        $expirationTime = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
        $sessionToken = Str::random(60);

        $session = Session::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => $sessionToken,
                'expires_at' => $expirationTime
            ]
        );

        $user->session_token = $sessionToken;
        $user->session_expiration = $expirationTime;
        $user->save();
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = $request->filled('remember_token');

        if ($this->attemptLogin($credentials, $remember)) {
            $user = Auth::user();
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $user->session_token,
                'session_expiration' => $user->session_expiration->toIso8601String(),
            ]);
        }

        return $this->handleLoginFailure($request);
    }

    public function handleLoginFailure(Request $request)
    {
        return response()->json(['message' => 'Login failed. Incorrect email or password.'], 401);
    }

    // ... Rest of the existing code in the LoginController

    public function cancelLogin()
    {
        session()->flash('message', 'Login process has been canceled.');
        return redirect()->back();
    }
}
