
<?php

use Illuminate\Http\Resources\Json\JsonResource;

class SuccessResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'message' => 'Email has been successfully verified.',
            'data' => $this->resource,
        ];
    }
}
