<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EfaktureStoreRequest extends FormRequest
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
     * Per-invoice validation happens in the controller so one bad invoice
     * lands in the failed list instead of rejecting the whole batch.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            '*' => 'array',
        ];
    }
}
