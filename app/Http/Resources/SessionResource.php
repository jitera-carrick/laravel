
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'session_token' => $this->session_token,
            'expires_at' => $this->expires_at,
            'id' => $this->id,
            'user_id' => $this->user_id,
        ];
    }
}
