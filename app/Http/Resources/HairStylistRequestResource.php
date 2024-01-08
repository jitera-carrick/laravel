
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        // Ensure to include the request_images relationship if loaded
        return [
            'id' => $this->id,
            'details' => $this->details,
            'status' => $this->status,
            'user_id' => $this->user_id,
            // Assuming 'request_image_id' is meant to be the ID of the first image or null if none exist
            'request_image_id' => $this->whenLoaded('request_images', function () {
                return $this->request_images->first()->id ?? null; // This line is unchanged, just included for context
            }),
            // Include the entire collection of images if needed
            'request_images' => RequestImageResource::collection($this->whenLoaded('request_images')),
            // Include the user relationship if loaded
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
