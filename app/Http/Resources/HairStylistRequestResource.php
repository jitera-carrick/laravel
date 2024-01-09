
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'service_details' => $this->service_details,
            'preferred_date' => $this->preferred_date,
            'preferred_time' => $this->preferred_time,
            'created_at' => $this->created_at, // Added 'created_at'
            'updated_at' => $this->updated_at, // Added 'updated_at'
            'details' => $this->details,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'request_image_id' => $this->request_image_id,
        ];
    }
}
