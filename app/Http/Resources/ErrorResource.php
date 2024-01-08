
<?php

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'error' => $this->resource['message'],
            'code' => $this->resource['status_code']
        ];
    }
}
