
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HairStylistRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'details' => $this->details,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'request_image_id' => $this->request_image_id,
        ];
    }
}
