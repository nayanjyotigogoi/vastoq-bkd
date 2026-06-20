<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFurnitureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [

            'name' => ['sometimes', 'string', 'max:255'],

            'category' => [
                'sometimes',
                'in:bed,mattress,sofa,dining_table,chair,study_table,wardrobe,refrigerator,washing_machine,air_conditioner,television,other'
            ],

            'description' => ['nullable', 'string'],

            'price_per_month' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'image_url' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'is_available' => [
                'sometimes',
                'boolean'
            ]
        ];
    }
}