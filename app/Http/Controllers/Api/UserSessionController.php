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

    /**
     * Handle password reset errors based on the provided error code.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePasswordResetError(Request $request)
    {
        $validated = $request->validate([
            'error_code' => 'required|string'
        ]);

        $errorCode = $validated['error_code'];
        $responseMessage = '';

        switch ($errorCode) {
            case 'expired_token':
                $responseMessage = 'Your password reset token has expired.';
                break;
            case 'invalid_token':
                $responseMessage = 'The password reset token is invalid.';
                break;
            default:
                return response()->json(['message' => 'Unknown error code.'], 400);
        }

        return response()->json(['status' => 200, 'message' => $responseMessage]);
    }

    // ... other methods ...
}
