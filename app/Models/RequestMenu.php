<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMenu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'menu_id',
        // Assuming 'menu' is a new column to be added, it should be listed here.
        'menu', // Add new column names to the $fillable array
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Assuming 'menu' is a new column with date/time to be cast, it should be listed here.
        // If 'menu' is not a date/time column, this line should be omitted.
        // 'menu' => 'datetime', // Add new date/time columns to the $casts array
    ];

    /**
     * Get the request that owns the RequestMenu.
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    /**
     * Get the menu that owns the RequestMenu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Assuming there are new relationships to be added, they should be defined here.
    // For example, if there's a new one-to-many relationship with a new table 'menu_details':
    /*
    public function menuDetails()
    {
        return $this->hasMany(MenuDetail::class, 'menu_id');
    }
    */
}
