<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMenuSelection extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_menu_selections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'menu_id',
    ];

    /**
     * Get the request that owns the RequestMenuSelection.
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    /**
     * Get the menu that owns the RequestMenuSelection.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
