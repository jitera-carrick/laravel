<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        DB::beginTransaction();
        try {
            $request = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Check if the request belongs to the logged-in user
            if ($request->user_id !== $user->id) {
                DB::rollBack(); // Ensure to rollback if not authorized
                return response()->json(['error' => 'You are not authorized to cancel this request.'], 403);
            }

            // Cascade delete related entries
            $request->requestAreas()->delete();
            $request->requestMenus()->delete();
            $request->images()->delete();
            
            $request->delete();
            DB::commit();

            return response()->json(['message' => 'Your request registration has been successfully canceled.'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while canceling the request.'], 500);
        }
    }

    /**
     * Edit a stylist request.
     *
     * @param HttpRequest $request
     * @param int $requestId
     * @return JsonResponse
     */
    public function editStylistRequest(HttpRequest $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'hair_concerns' => 'sometimes|max:3000',
            'images.*' => 'sometimes|file|mimes:png,jpg,jpeg|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $stylistRequest = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Authorization logic
            if ($stylistRequest->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized to edit this request.'], 403);
            }

            // Update hair concerns if provided
            if ($request->has('hair_concerns')) {
                $stylistRequest->hair_concerns = $request->input('hair_concerns');
            }

            // Update or create area
            if ($request->has('area_id')) {
                $areaId = $request->input('area_id');
                $stylistRequest->requestAreas()->delete(); // Remove old ones
                RequestArea::create([
                    'request_id' => $stylistRequest->id,
                    'area_id' => $areaId
                ]);
            }

            // Update or create menu
            if ($request->has('menu_id')) {
                $menuId = $request->input('menu_id');
                $stylistRequest->requestMenus()->delete(); // Remove old ones
                RequestMenu::create([
                    'request_id' => $stylistRequest->id,
                    'menu_id' => $menuId
                ]);
            }

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageModel = new Image();
                    $imageModel->file_path = $image->store('images');
                    $imageModel->file_size = $image->getSize();
                    $imageModel->file_format = $image->getClientOriginalExtension();
                    $stylistRequest->images()->save($imageModel);
                }
            }

            // Handle image deletion
            if ($request->has('delete_images')) {
                foreach ($request->input('delete_images') as $imageId) {
                    $image = Image::find($imageId);
                    if ($image && $image->request_id === $stylistRequest->id) {
                        $image->delete();
                    }
                }
            }

            $stylistRequest->save();
            DB::commit();

            return response()->json(['message' => 'Stylist request updated successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while editing the request.'], 500);
        }
    }

    // ... other methods ...
}
