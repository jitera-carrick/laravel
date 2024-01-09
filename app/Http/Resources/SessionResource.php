
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class SessionResource extends JsonResource
{
    public function toArray($request)
    {
        $response = parent::toArray($request);
        if (Arr::get($this->resource, 'error_message')) {
            $response['error_message'] = $this->resource['error_message'];
        }
        return $response;
    }
}
