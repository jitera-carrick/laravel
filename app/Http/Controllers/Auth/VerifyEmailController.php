<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        // The old code for verifying via user_id and remember_token is kept for backward compatibility
        $user_id = $request->input('user_id');
        $remember_token = $request->input('remember_token');

        if ($user_id && $remember_token) {
            $user = User::find($user_id);

            abort_if(!$user, 404, "User not found.");

            if ($user->remember_token !== $remember_token) {
                throw new ValidationException("Invalid token provided.");
            }

            $user->email_verified_at = Carbon::now();
            $user->remember_token = null;
            $user->updated_at = Carbon::now();
            $user->save();

            // Check if the request is from web
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email verified successfully.'], 200);
            } else {
                return redirect()->route('email_verified')->with('success', 'Email verified successfully.');
            }
        }

        // New code for verifying via token
        $token = $request->input('token'); // Retrieve the token from the URL parameter

        if ($token) {
            DB::beginTransaction();
            try {
                $verificationToken = EmailVerificationToken::where('token', $token)
                                    ->where('used', false)
                                    ->where('expires_at', '>', now())
                                    ->lockForUpdate() // Lock the row for the transaction
                                    ->first();

                if (!$verificationToken) {
                    DB::rollBack(); // Rollback the transaction if token is invalid or expired
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Invalid or expired token.'], 400);
                    } else {
                        return redirect()->route('verification_failed')->with('error', 'Invalid or expired token.');
                    }
                }

                $user = User::find($verificationToken->user_id);
                if ($user) {
                    $user->email_verified_at = now();
                    $user->save();

                    $verificationToken->used = true;
                    $verificationToken->save();

                    DB::commit(); // Commit the transaction

                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Email verified successfully.'], 200);
                    } else {
                        return redirect()->route('email_verified')->with('success', 'Email verified successfully.');
                    }
                } else {
                    // Handle the case where the user associated with the token cannot be found
                    DB::rollBack(); // Rollback the transaction
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'User not found.'], 404);
                    } else {
                        return redirect()->route('verification_failed')->with('error', 'User not found.');
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback the transaction on any exception
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'An error occurred during verification.'], 500);
                } else {
                    return redirect()->route('verification_failed')->with('error', 'An error occurred during verification.');
                }
            }
        } else {
            // Handle the case where no token is provided in the request
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No verification token provided.'], 400);
            } else {
                return redirect()->route('verification_failed')->with('error', 'No verification token provided.');
            }
        }
    }
}
