<?php

namespace DigitaldevLx\LaravelEupago\Http\Controllers;

use DigitaldevLx\LaravelEupago\Events\MBWayReferencePaid;
use DigitaldevLx\LaravelEupago\Http\Requests\MbWayCallbackRequest;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;

class MBWayController extends Controller
{
    /**
     * This endpoint is called when a MB Way reference is paid.
     *
     * @param MbWayCallbackRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function callback(MbWayCallbackRequest $request)
    {
        $validatedData = $request->validated();

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

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
