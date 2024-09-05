<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'brand_id' => 'required',
            'version_id' => 'required',
            'cat_id' => 'required',
            'subCat_id' => 'required',
            'modelYear' => 'required',
            'conditionPart' => 'required',
            'image' => 'required',
        ];
    }
}
