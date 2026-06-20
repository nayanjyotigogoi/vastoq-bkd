<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
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
     * Get validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

            'title' => ['sometimes', 'string', 'max:191'],

            'description' => ['nullable', 'string'],

            'property_type' => [
                'sometimes',
                'in:room,shared_room,flat,house,pg,office,shop,warehouse'
            ],

            'bhk_type' => [
                'sometimes',
                'in:na,1rk,2rk,1bhk,2bhk,3bhk,4bhk,5bhk'
            ],

            'furnishing' => [
                'sometimes',
                'in:unfurnished,semi_furnished,fully_furnished'
            ],

            'listing_class' => [
                'sometimes',
                'in:residential,commercial'
            ],

            'locality' => ['sometimes', 'string', 'max:100'],

            'city' => ['sometimes', 'string', 'max:100'],

            'pincode' => ['sometimes', 'string', 'max:20'],

            'address' => ['sometimes', 'string'],

            'rent_per_month' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'deposit' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'area_sqft' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'floor_number' => [
                'sometimes',
                'integer'
            ],

            'gender_preference' => [
                'sometimes',
                'in:male,female,family,any'
            ],

            'latitude' => [
                'sometimes',
                'numeric'
            ],

            'longitude' => [
                'sometimes',
                'numeric'
            ],

            'amenities' => [
                'sometimes',
                'array'
            ],

            'photos' => [
                'sometimes',
                'array'
            ],

            'is_broker' => [
                'sometimes',
                'boolean'
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'property_type.in' => 'Invalid property type selected.',
            'bhk_type.in' => 'Invalid BHK type selected.',
            'furnishing.in' => 'Invalid furnishing type selected.',
            'listing_class.in' => 'Invalid listing class selected.',
            'gender_preference.in' => 'Invalid gender preference selected.',
        ];
    }
}