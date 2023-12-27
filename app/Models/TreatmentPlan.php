<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'treatment_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'details',
        'stylist_id',
        'user_id',
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
     * Get the stylist that owns the treatment plan.
     */
    public function stylist()
    {
        return $this->belongsTo(Stylist::class, 'stylist_id');
    }

    /**
     * Get the user that owns the treatment plan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reservation associated with the treatment plan.
     */
    public function reservation()
    {
        return $this->hasOne(Reservation::class, 'treatment_plan_id');
    }
}
