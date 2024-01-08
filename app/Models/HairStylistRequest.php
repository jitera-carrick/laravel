<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairStylistRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hair_stylist_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requested_date',
        'service_type',
        'status',
        'additional_notes',
        'user_id',
        'details', // Keep existing fillable fields from the old code
        'request_image_id', // Keep existing fillable fields from the old code
        'created_at', // Keep existing fillable fields from the old code
        'updated_at', // Keep existing fillable fields from the old code
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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

    // Other existing relationships...

    // New relationships can be added below as needed.
}
