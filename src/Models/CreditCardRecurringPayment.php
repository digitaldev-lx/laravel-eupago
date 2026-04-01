<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\CreditCardRecurringPaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardRecurringPayment extends Model
{
    /** @use HasFactory<CreditCardRecurringPaymentFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'authorization_id',
        'transaction_id',
        'reference',
        'value',
        'status',
        'identifier',
        'message',
    ];

    /** @param  Builder<self>  $query
     *  @return Builder<self> */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'Paid');
    }

    /** @return BelongsTo<CreditCardRecurrenceAuthorization, $this> */
    public function authorization(): BelongsTo
    {
        return $this->belongsTo(CreditCardRecurrenceAuthorization::class, 'authorization_id');
    }
}
