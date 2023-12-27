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
}
