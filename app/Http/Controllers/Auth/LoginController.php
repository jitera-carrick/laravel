<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Exception;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
            'remember' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [
                'email' => $errors->first('email') ?: 'Invalid email format.',
                'password' => $errors->first('password') ?: 'Password must be at least 8 characters long.',
                'remember' => $errors->first('remember') ?: 'Invalid value for remember.',
            ];
            return response()->json(['error' => $errorMessages], 400);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);

        try {
            $result = $this->authService->authenticate($credentials, $remember);

            if ($result['success']) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful.',
                    'user' => [
                        'id' => $result['user']->id,
                        'email' => $result['user']->email,
                        'session_token' => $result['session_token'],
                        'session_expiration' => $result['session_expiration']->toDateTimeString(),
                    ]
                ], 200);
            } else {
                return response()->json(['error' => 'These credentials do not match our records.'], 401);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... Other methods remain unchanged ...

    // The recordLoginAttempt method from the new code is not conflicting with the existing code.
    // It can be added as a new method to the controller.
    public function recordLoginAttempt(Request $request)
    {
        // The recordLoginAttempt method from the new code is not conflicting with the existing code.
        // It can be added as a new method to the controller.
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $responseErrors = [];
            if ($errors->has('email')) {
                $responseErrors['email'] = "Invalid email format.";
            }
            if ($errors->has('password')) {
                $responseErrors['password'] = "Password must be at least 8 characters long.";
            }
            return response()->json(['errors' => $responseErrors], 400);
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed',
            ]);

            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }

        // If the credentials are correct but the login attempt is considered a failure for another reason
        // (e.g., account is not active), you should handle that logic here and log the attempt as failed.
        // For the sake of this example, we'll assume any other failure reason is not applicable.

        // If we reach this point, it means the login attempt has failed for a reason other than incorrect credentials.
        // Log the attempt as failed.
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => Carbon::now(),
            'success' => false,
            'status' => 'failed',
        ]);

        return response()->json(['status' => 200, 'message' => 'Login attempt recorded.'], 200);
    }

    // Other existing methods...
}
