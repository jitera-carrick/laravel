<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestImage extends Model
{
    use HasFactory;

    protected $table = 'request_images';

    protected $fillable = [
        'image_path',
        'request_id',
        'image_file', // New column added to fillable
        'image', // New column added to fillable
    ];

    protected $hidden = [
        // Existing hidden columns
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Assuming 'image_file' and 'image' are not date/time columns, no new casts needed
    ];

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    // Assuming there are no additional methods to be added
}
