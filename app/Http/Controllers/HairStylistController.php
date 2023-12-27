<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Resources\SuccessResource;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\CustomValidationException;

class HairStylistController extends Controller
{
    // ... (other methods)

    public function updateHairStylistRequest(UpdateHairStylistRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Validate that the user_id corresponds to a logged-in user
            $user = User::where('id', Auth::id())->where('is_logged_in', true)->firstOrFail();

            $hairStylistRequest = Request::where('id', $validatedData['request_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $this->authorize('update', $hairStylistRequest);

            // Validate hair_concerns text field
            if (strlen($validatedData['hair_concerns']) > 3000) {
                throw new CustomValidationException('The hair concerns text must not exceed 3000 characters.');
            }

            // Validate area_id and menu_id fields contain at least one selection each
            if (empty($validatedData['area_ids']) || empty($validatedData['menu_ids'])) {
                throw new CustomValidationException('At least one area and one menu must be selected.');
            }

            $hairStylistRequest->update([
                'hair_concerns' => $validatedData['hair_concerns'],
            ]);

            // Update request areas
            RequestArea::where('request_id', $hairStylistRequest->id)->delete();
            foreach ($validatedData['area_ids'] as $areaId) {
                RequestArea::create([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
            }

            // Update request menus
            RequestMenu::where('request_id', $hairStylistRequest->id)->delete();
            foreach ($validatedData['menu_ids'] as $menuId) {
                RequestMenu::create([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
            }

            // Validate and update images
            $currentImageCount = Image::where('request_id', $hairStylistRequest->id)->count();
            $newImageCount = count($validatedData['image_files']);
            if (($currentImageCount + $newImageCount) > 3) {
                throw new CustomValidationException('The total number of images cannot exceed three.');
            }

            foreach ($validatedData['image_files'] as $imageFile) {
                // Validate image format and size
                $allowedExtensions = ['png', 'jpg', 'jpeg'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                if (!in_array($imageFile->getClientOriginalExtension(), $allowedExtensions) || $imageFile->getSize() > $maxFileSize) {
                    throw new CustomValidationException('Each image must be a png, jpg, jpeg and not exceed 5MB in size.');
                }

                $image = new Image();
                $image->file_path = $imageFile->store('images', 'public');
                $image->file_size = $imageFile->getSize();
                $image->request_id = $hairStylistRequest->id;
                $image->save();
            }

            DB::commit();

            return new SuccessResource(['message' => 'Hair stylist request updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
