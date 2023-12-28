<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verify($user_id, $remember_token)
    {
        try {
            $user = User::findOrFail($user_id);

            abort_if($remember_token !== $user->remember_token, 401, 'Invalid token.');

            $user->email_verified_at = Carbon::now();
            $user->remember_token = null;
            $user->updated_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification failed.'], 500);
        }
    }
}
