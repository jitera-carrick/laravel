
<?php

use App\Events\FailedLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // ... other methods ...
}
