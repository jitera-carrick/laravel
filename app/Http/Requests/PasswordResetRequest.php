
<?php

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|string|email',
        ];
    }
}
