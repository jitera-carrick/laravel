<?php

use App\Events\FailedLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Check user's credentials
            // ...
            if ($loginFailed) {
                event(new FailedLogin($request->input('email')));
                return \App\Http\Responses\ApiResponse::loginFailure();
            }

            // ... rest of the login logic ...
        } catch (\Exception $e) {
            // Handle exception
        }
    }

    public function handleLoginFailure(): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => 'Login failed. Please check your email and password and try again.'
        ], 200);
    }

    // ... other methods ...
}

// Register the route for handling login failure
Route::get('/api/login/failure', [LoginController::class, 'handleLoginFailure']);
