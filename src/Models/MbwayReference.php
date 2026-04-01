<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\MbwayReferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MbwayReference extends Model
{
    /** @use HasFactory<MbwayReferenceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'reference',
        'value',
        'alias',
        'state',
        'transaction_id',
    ];

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', 1);
    }

    /** @return MorphTo<Model, $this> */
    public function mbwayable(): MorphTo
    {
        return $this->morphTo();
    }
}
