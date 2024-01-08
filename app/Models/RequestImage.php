
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'request_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_path',
        'request_id',
        'hair_stylist_request_id',
    ];

    protected $hidden = [
        // Keep the hidden array from the existing code
        // Usually, sensitive data is hidden. If there's any, add here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be handled for soft deletes.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the request that owns the request image.
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    /**
     * Get the hair stylist request that owns the request image.
     */
    public function hairStylistRequest()
    {
        return $this->belongsTo(HairStylistRequest::class, 'hair_stylist_request_id');
    }

    // The hairStylistRequests() method from the existing code has been commented out as it seems to be a mistake.
    // If it's needed, it should be corrected to reflect the correct relationship.
}
