<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairStylistRequest extends Model
{
    use HasFactory;

    protected $table = 'hair_stylist_requests';

    protected $fillable = [
        'details',
        'user_id',
        'request_image_id',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Add new date/time columns to the $casts array if needed
    ];

    /**
     * Get the user that owns the hair stylist request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the request images for the hair stylist request.
     */
    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'hair_stylist_request_id');
    }

    /**
     * Update the status of the hair stylist request to "canceled".
     *
     * @param int $requestId
     * @return bool
     */
    public function cancelRequest($requestId)
    {
        $request = $this->find($requestId);
        if ($request) {
            $request->status = 'canceled';
            return $request->save();
        }
        return false;
    }

    // Other relationships can be added here as needed.
}
