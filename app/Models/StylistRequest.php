
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'details', // Updated to include 'details' column
        'status',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Assuming 'details' is a JSON column, it should be cast to an array.
        'details' => 'array', // Added cast for 'details' column
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Other relationships can be added here as needed.

    /**
     * Update the stylist request with new details and status.
     *
     * @param string|null $details
     * @param string|null $status
     * @return void
     */
    public function updateStylistRequest($details = null, $status = null)
    {
        if ($details) $this->details = $details;
        if ($status) $this->status = $status;
        $this->save();
    }
}
