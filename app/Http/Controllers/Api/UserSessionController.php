<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MaintainUserSessionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\LoginAttempt;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserSessionController extends Controller
{
    use AuthenticatesUsers;

    // ... other methods ...

    public function maintainSession(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'remember_token' => 'sometimes|string'
        ]);

        $user = User::where('email', $validated['email'])->first();

        $sessionMaintained = false;

        if ($user && (!empty($validated['remember_token']) && $user->remember_token === $validated['remember_token'])) {
            // Assuming 'session_expiration' is a timestamp field on the 'users' table
            $user->session_expiration = now()->addMinutes(config('session.lifetime'));
            $user->save();
            $sessionMaintained = true;
        }

        return response()->json(['session_maintained' => $sessionMaintained]);
    }

    // ... other methods ...
}
