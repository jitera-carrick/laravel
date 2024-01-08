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
     * @var array<string, string>
     */
    protected $fillable = [
        'details',
        'status',
        'created_at',
        'updated_at',
    ];

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
     * Define the one-to-many relationship with Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'hair_stylist_request_id');
    }

    /**
     * Define the one-to-many relationship with RequestImages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'hair_stylist_request_id');
    }
}
