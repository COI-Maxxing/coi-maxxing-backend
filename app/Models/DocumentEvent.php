<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DocumentEvent extends Model
{
    use HasUuids, HasFactory, BelongsToCompany;

    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // this model cannot be updated
    const UPDATED_AT = null;

    protected $fillable = [
        'document_id',
        'company_id',
        'event_type',
        'actor',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'metadata' => 'array'
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException(
                'DocumentEvent records are immutable and cannot be updated.'
            );
        }

        return parent::save($options);
    }

    public function delete(): bool|null
    {
        throw new LogicException(
            'DocumentEvent records cannot be deleted. The audit log is permanent.'
        );
    }
}
