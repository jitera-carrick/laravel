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
        'hair_stylist_request_id', // New column added to fillable
    ];

    protected $hidden = [
        // Usually, sensitive data is hidden. If there's any, add here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    // New relationship added for hair_stylist_requests
    public function hairStylistRequest()
    {
        return $this->belongsTo(HairStylistRequest::class, 'hair_stylist_request_id');
    }
}
