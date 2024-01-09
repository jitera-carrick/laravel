
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request) // Update the toArray method
    {
        return [
            'id' => $this->id,
            'service_details' => $this->service_details, // Include 'service_details'
            'preferred_date' => $this->preferred_date, // Include 'preferred_date'
            'preferred_time' => $this->preferred_time, // Include 'preferred_time'
            'details' => $this->details,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'request_image_id' => $this->request_image_id,
        ];
    }
}
