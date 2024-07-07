<?php

namespace DigitaldevLx\LaravelEupago\Http\Controllers;

use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Http\Requests\MbCallbackRequest;
use DigitaldevLx\LaravelEupago\Models\MbReference;

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
            ->where('transaction_id', $validatedData['transacao'])
            ->where('state', 0)
            ->first();

        if (!$reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        event(new MBReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
