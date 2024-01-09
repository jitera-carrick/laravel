
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        // Ensure all necessary fields are included in the response
        $status = $this->status ?? 'pending'; // Set default status to 'pending' if not present
        return [
            'id' => $this->id,
            'service_details' => $this->service_details,
            'preferred_date' => $this->preferred_date,
            'preferred_time' => $this->preferred_time,
            // Use the default status if the status is not set
            // This is particularly useful for new requests
            'status' => $status,
            'user_id' => $this->user_id,
        ];
    }
}
