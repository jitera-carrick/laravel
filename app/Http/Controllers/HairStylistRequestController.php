<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon; // Import Carbon for date handling

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    // Add the new autoExpireRequests method
    public function autoExpireRequests(): JsonResponse
    {
        // ... existing autoExpireRequests method ...
    }

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // ... existing storeHairStylistRequest method ...
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // ... existing cancelHairStylistRequest method ...
    }

    // ... other methods ...

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing validateHairStylistRequest method ...
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        // ... existing editRequest method ...
    }

    // ... other methods ...

    // Update the updateHairStylistRequest method to meet the requirements
    public function updateHairStylistRequest(HttpRequest $request, $id): JsonResponse
    {
        // ... existing updateHairStylistRequest method ...
    }

    // ... other methods ...
}
