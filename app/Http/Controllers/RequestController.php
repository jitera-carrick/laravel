<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestImage;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function store(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'area' => 'required|exists:areas,name',
            'menu' => 'required|exists:menus,name',
            'hair_concerns' => 'sometimes|max:3000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
        ], [
            'area.required' => 'Please select a valid area.',
            'menu.required' => 'Please select a valid menu.',
            'hair_concerns.max' => 'Hair concerns and requests must be under 3000 characters.',
            'images.*.mimes' => 'Each image must be in png, jpg, or jpeg format.',
            'images.*.max' => 'Each image must be under 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $requestData = $validator->validated();
        $hairRequest = new Request([
            'area' => $requestData['area'],
            'menu' => $requestData['menu'],
            'hair_concerns' => $requestData['hair_concerns'],
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);
        $hairRequest->save();

        if (isset($requestData['images'])) {
            foreach ($requestData['images'] as $image) {
                $path = $image->store('request_images', 'public');
                RequestImage::create([
                    'image_path' => $path,
                    'request_id' => $hairRequest->id,
                ]);
            }
        }

        return response()->json(['status' => 201, 'request' => $hairRequest], 201);
    }
}
