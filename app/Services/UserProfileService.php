
<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Notifications\VerifyEmailNotification;

class UserProfileService
{
    public function updateUserProfile(UpdateHairStylistRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        $token = $this->generateEmailVerificationToken($user);
        $this->sendVerificationEmail($user, $token);

        return ['message' => 'User profile updated successfully.'];
    }

    private function generateEmailVerificationToken(User $user)
    {
        $token = new EmailVerificationToken([
            'token' => bin2hex(random_bytes(16)),
            'expires_at' => now()->addHours(24),
            'user_id' => $user->id,
        ]);
        $token->save();

        return $token;
    }

    private function sendVerificationEmail(User $user, EmailVerificationToken $token)
    {
        $user->notify(new VerifyEmailNotification($token));
    }
}
