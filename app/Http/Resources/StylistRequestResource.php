
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'preferred_date' => $this->preferred_date,
            'preferred_time' => $this->preferred_time,
            'stylist_preferences' => $this->stylist_preferences,
            'status' => $this->status,
        ];
    }
}
