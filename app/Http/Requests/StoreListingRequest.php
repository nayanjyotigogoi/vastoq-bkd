<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
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

        

            'title' => [
                'required',
                'string',
                'max:191'
            ],

            // 'owner_id' => [
            //     'required',
            //     'exists:users,id'
            // ],

            'description' => [
                'nullable',
                'string'
            ],

            'property_type' => [
                'required',
                'in:room,shared_room,flat,house,pg,office,shop,warehouse'
            ],

            'bhk_type' => [
                'required',
                'in:na,1rk,2rk,1bhk,2bhk,3bhk,4bhk,5bhk'
            ],

            'furnishing' => [
                'required',
                'in:unfurnished,semi_furnished,fully_furnished'
            ],

            'listing_class' => [
                'required',
                'in:residential,commercial'
            ],

            'locality' => [
                'required',
                'string',
                'max:100'
            ],

            'city' => [
                'required',
                'string',
                'max:100'
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:20'
            ],

            'address' => [
                'required',
                'string'
            ],

            'rent_per_month' => [
                'required',
                'integer',
                'min:0'
            ],

            'deposit' => [
                'nullable',
                'integer',
                'min:0'
            ],

            'area_sqft' => [
                'nullable',
                'integer',
                'min:0'
            ],

            'floor_number' => [
                'nullable',
                'integer'
            ],

            'gender_preference' => [
                'nullable',
                'in:male,female,family,any'
            ],

            'latitude' => [
                'nullable',
                'numeric'
            ],

            'longitude' => [
                'nullable',
                'numeric'
            ],

            'amenities' => [
                'nullable',
                'array'
            ],

            'photos' => [
                'nullable',
                'array'
            ],

            'is_broker' => [
                'nullable',
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