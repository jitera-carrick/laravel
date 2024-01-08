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
        'user_id', // Added from new code
        'request_image_id', // Added from new code
        'status',
        'created_at', // Retained from existing code
        'updated_at', // Retained from existing code
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Add new date/time columns to the $casts array if needed
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The existing users() relationship seems incorrect as it defines a one-to-many relationship
    // but the new code suggests a one-to-one relationship with User. Therefore, the new user() method is used.

    public function requestImage()
    {
        return $this->belongsTo(RequestImage::class, 'request_image_id');
    }

    // The existing requestImages() relationship is retained as it correctly defines a one-to-many relationship.
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
