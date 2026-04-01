<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use Carbon\Carbon;
use DigitaldevLx\LaravelEupago\MB\MB;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Mbable
{
    /** @return MorphMany<MbReference, $this> */
    public function mbReferences(): MorphMany
    {
        return $this->morphMany(MbReference::class, 'mbable');
    }

    /**
     * @return array<string|int, string>|MbReference
     */
    public function createMbReference(
        float $value,
        string $id,
        Carbon $startDate,
        Carbon $endDate,
        float $minValue,
        float $maxValue,
        bool $allowDuplication = false,
    ): array|MbReference {
        $mb = new MB(
            $value,
            $id,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $minValue,
            $maxValue,
            $allowDuplication
        );

        $mbReferenceData = $mb->create();

        if ($mb->hasErrors()) {
            return $mb->getErrors();
        }

        return $this->mbReferences()->create($mbReferenceData);
    }
}
