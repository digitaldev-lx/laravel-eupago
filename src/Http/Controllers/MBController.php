<?php

namespace DigitaldevLx\LaravelEupago\Http\Controllers;

use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Http\Requests\MbCallbackRequest;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Support\Facades\Log;

class MBController extends Controller
{
    /**
     * This endpoint is called when a MB reference is paid.
     *
     * @param MbCallbackRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function callback(MbCallbackRequest $request)
    {
        $validatedData = $request->validated();

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

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
