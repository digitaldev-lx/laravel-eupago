<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\CreditCardReferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditCardReference extends Model
{
    /** @use HasFactory<CreditCardReferenceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'transaction_id',
        'reference',
        'value',
        'redirect_url',
        'state',
        'callback_transaction_id',
        'customer_email',
    ];

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', 1);
    }

    /** @return MorphTo<Model, $this> */
    public function creditcardable(): MorphTo
    {
        return $this->morphTo();
    }
}
