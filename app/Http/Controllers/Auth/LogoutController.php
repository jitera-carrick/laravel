
<?php

use App\Http\Requests\LogoutRequest;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    // ... other methods ...

    /**
     * Log the user out by invalidating the session.
     *
     * @param LogoutRequest $request
     * @return JsonResponse
     */
    public function logout(LogoutRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $session = Session::where('session_token', $sessionToken)->first();
        $session->is_active = false;
        $session->updated_at = now();
        $session->save();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    // ... other methods ...
}
