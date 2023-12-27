<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Handle a cancel login request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelLogin()
    {
        // Redirect the user to the screen-tutorial route without altering the session or login state.
        return redirect()->route('screen-tutorial');
    }

    /**
     * Cancel the login process and return a message.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelLoginProcess()
    {
        // Return a JSON response with a cancellation message
        return response()->json([
            'message' => "Login cancelled. No changes were made."
        ]);
    }
}
