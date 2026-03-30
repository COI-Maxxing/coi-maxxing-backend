<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcontractor extends Model
{
    use HasFactory, HasUuids, BelongsToCompany;

    public $keyType = 'string';
    public $incrementing = false;
    // this model should not be timestamped
    const UPDATED_AT = null;

    protected $fillable = [
        'company_id', // allowed for seeding only
        'business_name',
        'contact_name',
        'contact_email',
        'contact_phone'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime'
        ];
    }

    // get the company that owns this subcontractor
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    // get the documents submitted by this subcontractor
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'subcontractor_id', 'id');
    }

    // get all of the upload requests by this subcontractor
    public function upload_requests(): HasMany
    {
        return $this->hasMany(UploadRequest::class, 'subcontractor_id', 'id');
    }
}
