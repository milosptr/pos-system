<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyOrderStoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            '*' => 'required|array',
            '*.kolicina' => 'required|numeric',
            '*.cena' => 'required|numeric',
            '*.naziv' => 'required|string',
            '*.datum' => 'required|string',
            '*.sto' => 'required|string',
            '*.porudzbinaid' => 'required|integer',
            '*.jm' => 'nullable|string',
            '*.stampanjenalogid' => 'nullable|integer',
            '*.modifikatorslobodan' => 'nullable|string',
        ];
    }
}
