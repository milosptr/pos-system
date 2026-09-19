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
     * All validation happens in the controller: a bad invoice lands in the
     * failed list instead of rejecting the batch, and a body that is not a
     * list of invoices gets an error that says so, rather than Laravel's
     * "The 0 must be an array."
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [];
    }
}
