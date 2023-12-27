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
    public function createStylistRequest(HttpRequest $httpRequest)
    {
        $validator = Validator::make($httpRequest->all(), [
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|exists:request_areas,area_id',
            'menu_id' => 'required|exists:request_menus,menu_id',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array|max:3',
            'images.*.file_path' => 'required|string',
            'images.*.file_format' => 'required|in:png,jpg,jpeg',
            'images.*.file_size' => 'required|integer|max:5120', // 5MB in kilobytes
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::where('id', $httpRequest->user_id)->where('is_logged_in', true)->firstOrFail();

            $newRequest = new Request([
                'user_id' => $user->id,
                'status' => 'available',
                'hair_concerns' => $httpRequest->hair_concerns,
            ]);
            $newRequest->save();

            foreach ($httpRequest->area_id as $areaId) {
                $newRequestArea = new RequestArea([
                    'request_id' => $newRequest->id,
                    'area_id' => $areaId,
                ]);
                $newRequestArea->save();
            }

            foreach ($httpRequest->menu_id as $menuId) {
                $newRequestMenu = new RequestMenu([
                    'request_id' => $newRequest->id,
                    'menu_id' => $menuId,
                ]);
                $newRequestMenu->save();
            }

            foreach ($httpRequest->images as $imageData) {
                $newImage = new Image([
                    'request_id' => $newRequest->id,
                    'file_path' => $imageData['file_path'],
                    'file_size' => $imageData['file_size'],
                    'file_format' => $imageData['file_format'],
                ]);
                $newImage->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'New stylist request created successfully.',
                'request' => $newRequest,
            ], 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'User not found or not logged in.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while creating the request.'], 500);
        }
    }

    // ... other methods ...

    /**
     * Edit a stylist request.
     *
     * @param HttpRequest $httpRequest
     * @param int $requestId
     * @return JsonResponse
     */
    // ... The editStylistRequest method remains unchanged ...

    // ... other methods ...
}
