
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        // Ensure all necessary fields are included in the response
        // Add confirmation message to the response
        return [
            'id' => $this->id,
            'service_details' => $this->service_details,
            'preferred_date' => $this->preferred_date,
            'preferred_time' => $this->preferred_time,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'confirmation_message' => 'Your stylist request has been received and is being processed.',
        ];
    }
}
