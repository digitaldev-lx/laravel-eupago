<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Http\Controllers;

use DigitaldevLx\LaravelEupago\Enums\PaymentMethod;
use DigitaldevLx\LaravelEupago\Events\ApplePayReferencePaid;
use DigitaldevLx\LaravelEupago\Events\CallbackReceived;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferencePaid;
use DigitaldevLx\LaravelEupago\Events\GooglePayReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBWayReferencePaid;
use DigitaldevLx\LaravelEupago\Http\Requests\CallbackRequest;
use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function callback(CallbackRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $validatedData */
        $validatedData = $request->validated();

        event(new CallbackReceived($validatedData, (string) $validatedData['mp']));

        $paymentMethod = PaymentMethod::from((string) $validatedData['mp']);

        [$modelClass, $eventClass, $updateFields] = match ($paymentMethod) {
            PaymentMethod::Multibanco => [
                MbReference::class,
                MBReferencePaid::class,
                ['state' => 1, 'transaction_id' => $validatedData['transacao']],
            ],
            PaymentMethod::MBWay => [
                MbwayReference::class,
                MBWayReferencePaid::class,
                ['state' => 1],
            ],
            PaymentMethod::CreditCard => [
                CreditCardReference::class,
                CreditCardReferencePaid::class,
                ['state' => 1, 'callback_transaction_id' => $validatedData['transacao']],
            ],
            PaymentMethod::GooglePay => [
                GooglePayReference::class,
                GooglePayReferencePaid::class,
                ['state' => 1, 'callback_transaction_id' => $validatedData['transacao']],
            ],
            PaymentMethod::ApplePay => [
                ApplePayReference::class,
                ApplePayReferencePaid::class,
                ['state' => 1, 'callback_transaction_id' => $validatedData['transacao']],
            ],
        };

        /** @var MbReference|MbwayReference|CreditCardReference|GooglePayReference|ApplePayReference|null $reference */
        $reference = $modelClass::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'], 404);
        }

        $reference->update($updateFields);

        Log::info('EuPago Update State', [class_basename($modelClass) => $reference]);

        event(new $eventClass($reference));

        return response()->json(['response' => 'Success'], 200);
    }
}
