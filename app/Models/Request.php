
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'stylist_request_id',
        'area',
        'menu', 'details', 'priority',
        'hair_concerns',
        'status',
        'user_id',
        // Add new column names to the $fillable array if needed
    ];

    protected $hidden = [
        // Existing hidden columns
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Assuming 'details' is a text field and does not require casting
        // Assuming 'priority' is a string and does not require casting
        // Add new date/time columns to the $casts array if needed
        // 'priority' => 'string', // Uncomment if 'priority' needs to be casted
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stylistRequest()
    {
        return $this->belongsTo(StylistRequest::class, 'stylist_request_id');
    }

    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'request_id');
    }

    public function requestAreas()
    {
        return $this->hasMany(RequestArea::class, 'request_id');
    }

    public function requestMenus()
    {
        return $this->hasMany(RequestMenu::class, 'request_id');
    }

    // Add new relationships if there are any new tables related to this model
}
