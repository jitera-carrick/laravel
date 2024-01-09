
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
        'service_details', // New field from new code
        'preferred_date', // New field from new code
        // 'status' => 'pending', // Set default status to 'pending' (Removed, incorrect syntax for $fillable)
        'preferred_time', // New field from new code
        'status', // Common field in both versions
        'user_id', // Common field in both versions
        'created_at', // Common field in both versions
        'updated_at', // Common field in both versions
    ];
 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */ 
    protected $hidden = [
        // No hidden fields specified in both versions
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [ // Corrected cast types
        'preferred_date' => 'date', // Corrected cast from new code
        'preferred_time' => 'string', // Corrected cast from new code
        'status' => 'enum:pending,confirmed,canceled', // Add cast for status as enum
        'created_at' => 'datetime', // Common cast in both versions
        'updated_at' => 'datetime', // Common cast in both versions
    ];

    /**
     * Define the many-to-one relationship with User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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

    /**
     * Create a new hair stylist request with the provided data.
     *
     * @param array $data
     * @return \App\Models\HairStylistRequest
     */
    public static function createRequest(array $data)
    {
        // Ensure 'status' is set to 'pending' if not provided
        $data['status'] = $data['status'] ?? 'pending';

        // Create and return the new hair stylist request
        return self::create($data);
    }

    // Other existing relationships and methods from the old code should be included here.
    // Ensure that no existing functionality is removed.
}
