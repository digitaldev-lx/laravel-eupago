<?php

namespace DigitaldevLx\LaravelEupago\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CallbackRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Log::info(
            'EuPago Callback',
            [
                'url' => $this->fullUrl(),
                'payload' => $this->all()
            ]
        );

        $parentRules = [
            'valor' => 'required',
            'canal' => [
                'required',
                Rule::in([config('eupago.channel')])
            ],
            'referencia' => 'required',
            'transacao' => 'required',
            'identificador' => 'required',
            'mp' => 'required',
            'chave_api' => [
                'required',
                Rule::in([config('eupago.api_key')])
            ],
            'data' => 'required|date_format:Y-m-d:H:i:s',
            'entidade' => 'required',
            'comissao' => 'required',
            'local' => 'nullable',
        ];

        if($this["mp"] == 'PC:PT'){
            $parentRules['refrencia'][] = Rule::exists('mb_references', 'reference');
        }elseif ($this["mp"] == 'MW:PT'){
            $parentRules['refrencia'][] = Rule::exists('mbway_references', 'reference');
        }

        return $parentRules;
    }
}
