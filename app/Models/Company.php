<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // generates a uuid for this model
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

// this model represents the general contractor
class Company extends Model
{
    use HasFactory, HasUuids;

    // this model should not be timestamped
    public $timestamps = false;

    // assignable attributes
    protected $fillable = [
        'name',
    ];

    // attributes that must be cast
    protected $casts = [
        'created_at' => 'datetime',
    ];

    // get all users belonging to this company
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    // get all subcontractors of this company
    public function subcontractors(): HasMany
    {
        return $this->hasMany(Subcontractor::class, 'company_id', 'id');
    }
}
