<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\CreditCardRecurrenceAuthorizationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditCardRecurrenceAuthorization extends Model
{
    /** @use HasFactory<CreditCardRecurrenceAuthorizationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'subscription_id',
        'reference_subs',
        'redirect_url',
        'status',
        'identifier',
    ];

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopeAuthorized(Builder $query): Builder
    {
        return $query->where('status', 'Authorized');
    }

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'Pending');
    }

    /** @return MorphTo<Model, $this> */
    public function creditcardrecurrable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<CreditCardRecurringPayment, $this> */
    public function recurringPayments(): HasMany
    {
        return $this->hasMany(CreditCardRecurringPayment::class, 'authorization_id');
    }
}
