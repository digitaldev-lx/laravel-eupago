<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Mbwayable
{
    /** @return MorphMany<MbwayReference, $this> */
    public function mbwayReferences(): MorphMany
    {
        return $this->morphMany(MbwayReference::class, 'mbwayable');
    }
}
