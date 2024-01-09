
<?php

use App\Http\Middleware\Authenticate;
use App\Models\LoginAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authenticate::class);
    }

    public function cancelLogin(): JsonResponse
    {
        $user = Auth::user();
        LoginAttempt::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Your login process has been canceled.'
        ]);
    }
}
