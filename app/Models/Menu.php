<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description', // Keep the 'description' from the existing code.
        'created_at', // Added from the new code.
        'updated_at', // Added from the new code.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // No hidden attributes are specified in either version of the code.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the one-to-many relationship with RequestMenu.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestMenus()
    {
        return $this->hasMany(RequestMenu::class, 'menu_id');
    }

    /**
     * Define the one-to-many relationship with RequestMenuSelection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestMenuSelections()
    {
        // Assuming RequestMenuSelection is a different model that also relates to Menu.
        // If it's the same as RequestMenu, then only one relationship method should be kept.
        return $this->hasMany(RequestMenuSelection::class, 'menu_id');
    }
}
