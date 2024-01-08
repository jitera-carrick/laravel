
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Http\Resources\RequestResource;
use Illuminate\Http\JsonResponse;

class StylistRequestController extends Controller
{
    protected $stylistRequestService;

    public function __construct(StylistRequestService $stylistRequestService)
    {
        $this->stylistRequestService = $stylistRequestService;
    }

    public function createStylistRequest(CreateStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $stylistRequestId = $this->stylistRequestService->createRequest($validatedData);

        return response()->json([
            'stylist_request_id' => $stylistRequestId,
            'message' => 'Stylist request created successfully.'
        ], 201);
    public function createHairStylistRequest(CreateHairStylistRequest $request)
    {
        $validated = $request->validated();
        $hairRequest = new Request($validated);
        $hairRequest->status = $validated['status'] ?? 'pending';
        $hairRequest->save();

        if (isset($validated['area_id'])) {
            foreach ($validated['area_id'] as $areaId) {
                RequestArea::create(['request_id' => $hairRequest->id, 'area_id' => $areaId]);
            }
        }

        if (isset($validated['menu_id'])) {
            foreach ($validated['menu_id'] as $menuId) {
                RequestMenu::create(['request_id' => $hairRequest->id, 'menu_id' => $menuId]);
            }
        }

        return new RequestResource($hairRequest);
    }

    }

    // ... other methods ...
}
