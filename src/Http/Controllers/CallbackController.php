<?php

namespace DigitaldevLx\LaravelEupago\Http\Controllers;

use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBWayReferencePaid;
use DigitaldevLx\LaravelEupago\Http\Requests\CallbackRequest;
use DigitaldevLx\LaravelEupago\Http\Requests\MbCallbackRequest;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    /**
     * This endpoint is called when a MB reference is paid.
     *
     * @param CallbackRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function callback(CallbackRequest $request)
    {
        $validatedData = $request->validated();

        if($validatedData['mp'] == 'PC:PT'){
            $reference = MbReference::where('reference', $validatedData['referencia'])
                ->where('value', $validatedData['valor'])
                ->where('state', 0)
                ->first();

            if (!$reference) {
                return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
            }

            $reference->update(['state' => 1, 'transaction_id' => $validatedData['transacao']]);

            Log::info(
                'EuPago Update State',
                [
                    'MBReference' => $reference
                ]
            );

            event(new MBReferencePaid($reference));

        }elseif ($validatedData['mp'] == 'MW:PT'){

            $reference = MbwayReference::where('reference', $validatedData['referencia'])
                ->where('value', $validatedData['valor'])
                ->where('state', 0)
                ->first();

            if (!$reference) {
                return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
            }

            $reference->update(['state' => 1]);

            // trigger event
            event(new MBWayReferencePaid($reference));

        }

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
