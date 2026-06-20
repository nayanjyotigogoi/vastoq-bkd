<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFurnitureEnquiryRequest extends FormRequest
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

            'furniture_id' => [
                'required',
                'exists:furniture,id'
            ],

            'user_id' => [
                'nullable',
                'exists:users,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'phone' => [
                'required',
                'string',
                'max:20'
            ],

            'locality' => [
                'required',
                'string',
                'max:255'
            ],

            'message' => [
                'nullable',
                'string'
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
            'furniture_id.required' => 'Furniture item is required.',
            'furniture_id.exists' => 'Selected furniture item does not exist.',

            'name.required' => 'Name is required.',

            'phone.required' => 'Phone number is required.',

            'locality.required' => 'Locality is required.',
        ];
    }
}