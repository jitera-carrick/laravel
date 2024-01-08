
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'details' => $this->details,
            'status' => $this->status,
            'area' => $this->whenLoaded('requestAreas', function () {
                return $this->requestAreas->pluck('area_name');
            }),
            'menu' => $this->whenLoaded('requestMenus', function () {
                return $this->requestMenus->pluck('menu_name');
            }),
            'hair_concerns' => $this->hair_concerns,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include any other fields that are required
        ];
    }
}
