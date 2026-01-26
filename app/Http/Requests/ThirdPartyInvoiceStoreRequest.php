<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyInvoiceStoreRequest extends FormRequest
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
            '*.brojracuna' => 'required|string',
            '*.jm' => 'nullable|string',
            '*.sto' => 'nullable|string',
            '*.gotovina' => 'nullable|numeric',
            '*.kartica' => 'nullable|numeric',
            '*.prenosnaracun' => 'nullable|numeric',
            '*.porudzbinaid' => 'nullable|integer',
            '*.stornoporudzbine' => 'nullable|integer',
            '*.popust' => 'nullable|numeric',
            '*.originalnacena' => 'nullable|numeric',
        ];
    }
}
