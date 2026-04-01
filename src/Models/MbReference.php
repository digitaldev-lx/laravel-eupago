<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\MbReferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MbReference extends Model
{
    /** @use HasFactory<MbReferenceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'entity',
        'reference',
        'value',
        'start_date',
        'end_date',
        'min_value',
        'max_value',
        'state',
        'transaction_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', 1);
    }

    /** @return MorphTo<Model, $this> */
    public function mbable(): MorphTo
    {
        return $this->morphTo();
    }
}
