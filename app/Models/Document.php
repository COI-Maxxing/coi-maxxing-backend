<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory, HasUuids, BelongsToCompany;

    public $keyType = 'string';
    public $incrementing = false;

    public $fillable = [
        'subcontractor_id',
        'document_type',
        'file_url',
        'uploaded_by',
        'insurer',
        'policy_number',
        'coverage_amount',
        'expiry_date',
        'holder_name',
        'ai_raw_response'
    ];

    protected function casts(): array
    {
        return [
            'coverage_amount' => 'decimal:2',
            'expiry_date' => 'date',
            'ai_raw_response' => 'array'
        ];
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DocumentEvent::class)->orderBy('created_at', 'asc');
    }

    /**
     * record an immutable audit event for this document
     */
    public function logEvent(string $eventType, string $actor, ?array $metadata = null): DocumentEvent
    {
        return $this->events()->create([
            'company_id' => $this->company_id,
            'event_type' => $eventType,
            'actor' => $actor,
            'metadata' => $metadata
        ]);
    }
}
