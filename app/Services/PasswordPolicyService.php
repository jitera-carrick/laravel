<?php
namespace App\Services;

use App\Models\PasswordPolicy;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PasswordPolicyService {
    // ... (other methods in the service)

    /**
     * Update the password policy settings in the database.
     *
     * @param array $settings
     * @return string
     */
    public function updatePasswordPolicy(array $settings): string
    {
        // Validate the new settings
        $validator = Validator::make($settings, [
            'minimum_length' => 'required|integer|min:1',
            'require_digits' => 'required|boolean',
            'require_letters' => 'required|boolean',
            'require_special_characters' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return "Validation failed: " . implode(", ", $validator->errors()->all());
        }

        try {
            // Assuming there's only one password policy record
            $passwordPolicy = PasswordPolicy::first();
            if (!$passwordPolicy) {
                // Handle the case where no password policy exists
                $passwordPolicy = new PasswordPolicy();
            }

            // Update the password policy with new settings
            $passwordPolicy->fill($settings);
            $passwordPolicy->save();

            return "Password policy has been successfully updated.";
        } catch (Exception $e) {
            // Log the exception
            Log::error('Failed to update password policy: ' . $e->getMessage());
            return "Failed to update password policy: " . $e->getMessage();
        }
    }

    // ... (other methods in the service)
}
