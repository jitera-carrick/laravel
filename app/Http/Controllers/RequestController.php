
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use App\Models\StylistRequest; // Added line
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $hairRequest = new Request([
            'hair_concerns' => $validated['hair_concerns'],
            'priority' => $validated['priority'] ?? 'normal', // Assuming 'normal' is the default priority
            'status' => $validated['status'] ?? 'pending',
            'user_id' => Auth::id(),
        ]);
        $hairRequest->save();

        if (isset($validated['area']) && is_array($validated['area'])) {
            foreach ($validated['area'] as $areaId) {
                RequestAreaSelection::create([
                    'area_name' => $areaId, // Assuming 'area_name' is the correct field
                    'request_id' => $hairRequest->id,
                ]);
            }
        }

        if (isset($validated['menu']) && is_array($validated['menu'])) {
            foreach ($validated['menu'] as $menuId) {
                RequestMenuSelection::create([ // Assuming 'RequestMenuSelection' is the correct model
                    'menu_id' => $menuId,
                    'request_id' => $hairRequest->id,
                ]);
            }
        }

        // Assuming images are uploaded and 'image_path' is the correct field
        if (isset($validated['image_paths']) && is_array($validated['image_paths'])) {
            foreach ($validated['image_paths'] as $imagePath) {
                RequestImage::create([
                    'request_id' => $hairRequest->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        return Response::json([
            'status' => 'success',
            'request_id' => $hairRequest->id,
            'message' => 'Hair stylist request created successfully.',
        ]);
    }

    // ... other methods ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        // ... existing deleteImage method code ...
    }

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // ... existing cancelHairStylistRequest method code ...
    }

    // ... other methods ...
}
