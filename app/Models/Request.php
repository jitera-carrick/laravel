<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'status',
        'hair_concerns',
        'user_id',
    ];

    /**
     * Get the user that owns the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the request areas for the request.
     */
    public function requestAreas()
    {
        return $this->hasMany(RequestArea::class);
    }

    /**
     * Get the request menus for the request.
     */
    public function requestMenus()
    {
        return $this->hasMany(RequestMenu::class);
    }

    /**
     * Get the images for the request.
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
