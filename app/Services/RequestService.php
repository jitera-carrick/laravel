
<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestImage;
use Exception;
use Illuminate\Support\Facades\DB;

class RequestService
{
    public function deleteRequestImage(int $request_id, string $image_path): bool
    {
        $image = RequestImage::where('request_id', $request_id)
                             ->where('image_path', $image_path)
                             ->first();

        if ($image) {
            DB::transaction(function () use ($image, $request_id) {
                $image->delete();
                Request::where('id', $request_id)->update(['updated_at' => now()]);
            });
            return true;
        }

        return false;
    }
}
