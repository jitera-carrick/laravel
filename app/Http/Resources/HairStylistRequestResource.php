
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        // Format the response to include specified fields
        return [
            'id' => $this->id,
            'requested_date' => $this->requested_date,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'additional_notes' => $this->additional_notes,
            // Include the entire collection of images if needed
            'request_images' => RequestImageResource::collection($this->whenLoaded('request_images')),
            // Include the user relationship if loaded
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
