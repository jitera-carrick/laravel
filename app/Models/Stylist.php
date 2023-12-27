<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stylist extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stylists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile',
        'user_id',
        'message_id', // Added new fillable attribute from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, sensitive fields are hidden. Add any fields that need to be hidden here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Add any fields that need to be cast here.
        'created_at' => 'datetime', // Added cast for new attribute from new code
        'updated_at' => 'datetime', // Added cast for new attribute from new code
    ];

    /**
     * Get the user that the stylist belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the message that the stylist is associated with.
     */
    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    /**
     * Get the treatment plans for the stylist.
     */
    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'stylist_id');
    }
}
