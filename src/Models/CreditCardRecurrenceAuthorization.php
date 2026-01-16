<?php

namespace DigitaldevLx\LaravelEupago\Models;

use DigitaldevLx\LaravelEupago\Database\Factories\CreditCardRecurrenceAuthorizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardRecurrenceAuthorization extends Model
{
    use HasFactory;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'subscription_id',
        'reference_subs',
        'redirect_url',
        'status',
        'identifier',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scopes a query to only include authorized subscriptions.
     *
     * @param $query
     * @return mixed
     */
    public function scopeAuthorized($query)
    {
        return $query->where('status', 'Authorized');
    }

    /**
     * Scopes a query to only include pending subscriptions.
     *
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the owning creditcardrecurrable model.
     */
    public function creditcardrecurrable()
    {
        return $this->morphTo();
    }

    /**
     * Get the recurring payments for this authorization.
     */
    public function recurringPayments()
    {
        return $this->hasMany(CreditCardRecurringPayment::class, 'authorization_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CreditCardRecurrenceAuthorizationFactory::new();
    }
}
