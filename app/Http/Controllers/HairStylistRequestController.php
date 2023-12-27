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

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // The existing storeHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // The existing cancelHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // The existing validateHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        // The existing editRequest method is kept as it is.
        // ... existing code ...
    }

    // New method createHairStylistRequest is added to meet the requirement
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for createHairStylistRequest method ...
    }

    // ... other methods ...
}
