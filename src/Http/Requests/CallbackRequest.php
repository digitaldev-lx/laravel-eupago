<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'valor' => 'required',
            'canal' => [
                'required',
                Rule::in([config('eupago.channel')]),
            ],
            'referencia' => ['required'],
            'transacao' => 'required',
            'identificador' => 'required',
            'mp' => 'required',
            'chave_api' => [
                'required',
                Rule::in([config('eupago.api_key')]),
            ],
            'data' => 'required|date_format:Y-m-d:H:i:s',
            'entidade' => 'required',
            'comissao' => 'required',
            'local' => 'nullable',
        ];
    }

    protected function passedValidation(): void
    {
        Log::info('EuPago Callback', [
            'url' => $this->fullUrl(),
            'payload' => $this->all(),
        ]);
    }
}
