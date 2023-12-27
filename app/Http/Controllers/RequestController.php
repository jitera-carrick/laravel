<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\StoreYourSpecificRequest;

class RequestController extends Controller
{
    // ... other methods ...

    /**
     * Cancel a stylist request.
     *
     * @param int $requestId
     * @return JsonResponse
     */
    public function cancelStylistRequest($requestId)
    {
        $user = auth()->user(); // Retrieve the currently authenticated user

        if (!$user || !$user->is_logged_in) {
            return response()->json(['error' => 'User must be logged in to cancel requests.'], 401);
        }

        DB::beginTransaction();
        try {
            $request = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Check if the request belongs to the logged-in user
            if ($request->user_id !== $user->id) {
                DB::rollBack(); // Ensure to rollback if not authorized
                return response()->json(['error' => 'You are not authorized to cancel this request.'], 403);
            }

            // Update the status of the request to 'canceled' instead of deleting it
            $request->status = 'canceled';
            $request->save();

            // Cascade delete related entries
            $request->requestAreas()->delete();
            $request->requestMenus()->delete();
            $request->images()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Your request has been successfully canceled.',
                'request_id' => $request->id,
                'status' => $request->status
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while canceling the request.'], 500);
        }
    }

    // ... other methods ...

    /**
     * Create a new stylist request.
     *
     * @param HttpRequest $httpRequest
     * @return JsonResponse
     */
    // ... The createStylistRequest method remains unchanged ...

    /**
     * Edit an existing stylist request.
     *
     * @param StoreYourSpecificRequest $request
     * @param int $requestId
     * @return JsonResponse
     */
    public function editStylistRequest(StoreYourSpecificRequest $request, $requestId)
    {
        $user = auth()->user(); // Retrieve the currently authenticated user

        if (!$user || !$user->is_logged_in) {
            return response()->json(['error' => 'Authentication failed or user not logged in.'], 401);
        }

        DB::beginTransaction();
        try {
            $stylistRequest = Request::with(['requestAreas', 'requestMenus', 'images'])
                ->where('user_id', $user->id)
                ->findOrFail($requestId);

            $validatedData = $request->validated();

            // Update hair concerns
            $stylistRequest->hair_concerns = $validatedData['hair_concerns'];
            $stylistRequest->save();

            // Update request areas
            $stylistRequest->requestAreas()->delete();
            foreach ($validatedData['area_id'] as $areaId) {
                RequestArea::create([
                    'request_id' => $stylistRequest->id,
                    'area_id' => $areaId,
                ]);
            }

            // Update request menus
            $stylistRequest->requestMenus()->delete();
            foreach ($validatedData['menu_id'] as $menuId) {
                RequestMenu::create([
                    'request_id' => $stylistRequest->id,
                    'menu_id' => $menuId,
                ]);
            }

            // Handle images (upload new images and delete removed images)
            // This is a placeholder for the image handling logic
            // ...

            $stylistRequest->touch(); // Update the updated_at timestamp

            DB::commit();

            return response()->json([
                'request_id' => $requestId,
                'status' => 'updated',
                'message' => 'Your request has been successfully updated.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while updating the request.'], 500);
        }
    }

    // ... other methods ...
}
