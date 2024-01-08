<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestImage extends Model
{
    use HasFactory;

    protected $table = 'request_images'; // Keep the explicit table definition

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'image_path',
        'request_id', // Keep the existing 'request_id' from the old code
        'hair_stylist_request_id', // Keep the new 'hair_stylist_request_id' from the new code
    ];

    protected $hidden = [
        // Keep the hidden array from the existing code
        // Usually, sensitive data is hidden. If there's any, add here.
    ];

    protected $casts = [
        'created_at' => 'datetime', // Keep the casts from the existing code
        'updated_at' => 'datetime',
    ];

    /**
     * Get the request that owns the request image.
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id'); // Keep the existing relationship
    }

    /**
     * Get the hair stylist request that owns the request image.
     */
    public function hairStylistRequest()
    {
        return $this->belongsTo(HairStylistRequest::class, 'hair_stylist_request_id'); // Keep the new relationship from the new code
    }

    /**
     * Get the hair stylist requests associated with the request image.
     * This seems to be a mistake in the existing code as it implies a one-to-many relationship
     * from the RequestImage to HairStylistRequest, which doesn't make sense given the foreign key
     * 'hair_stylist_request_id' on the RequestImage model. This method should be removed or corrected.
     * For now, we'll comment it out to avoid confusion and potential bugs.
     */
    // public function hairStylistRequests()
    // {
    //     return $this->hasMany(HairStylistRequest::class, 'request_image_id');
    // }
}
