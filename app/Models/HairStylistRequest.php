
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

    /**
     * Unlink the request image from the hair stylist request.
     *
     * @param int $image_id
     * @return bool
     */
    public function unlinkRequestImage($image_id)
    {
        $linkedRequests = $this->where('request_image_id', $image_id)->get();

        if ($linkedRequests->isEmpty()) {
            return false;
        }

        $linkedRequests->each->update(['request_image_id' => null]);
        return true;
    }
}
