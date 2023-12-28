<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAreaSelection extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_area_selections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'area_id',
    ];

    /**
     * Get the request associated with the area selection.
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    /**
     * Get the area associated with the area selection.
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
