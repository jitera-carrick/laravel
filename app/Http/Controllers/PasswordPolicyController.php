<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordPolicyRequest;
use App\Services\PasswordPolicyService;
use Illuminate\Http\JsonResponse;

class PasswordPolicyController extends Controller
{
    protected $passwordPolicyService;

    public function __construct(PasswordPolicyService $passwordPolicyService)
    {
        $this->passwordPolicyService = $passwordPolicyService;
    }

    public function update(UpdatePasswordPolicyRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $this->passwordPolicyService->updatePasswordPolicy($validatedData);
            return response()->json(['message' => 'Password policy updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update password policy.'], 500);
        }
    }
}
