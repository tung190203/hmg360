<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ép user() luôn lấy từ guard guest
    public function user($guard = null)
    {
        return auth('guest')->user();
    }

    public function rules(): array
    {
        $guestId = $this->user()?->id; // hoặc auth('guest')->id()

        return [
            'name' => 'required|string|max:255',
            'identification_number' => 'nullable|string|max:20|unique:guests,identification_number,' . $guestId,
            'email' => 'required|email:rfc,dns|unique:guests,email,' . $guestId,
            'nation_id' => 'nullable|exists:nations,id',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|numeric',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.name.required'),
            'name.string' => __('validation.name.string'),
            'name.max' => __('validation.name.max'),
    
            'email.required' => __('validation.email.required'),
            'email.email' => __('validation.email.email'),
            'email.unique' => __('validation.email.unique'),
    
            'password.required' => __('validation.password.required'),
            'password.string' => __('validation.password.string'),
            'password.min' => __('validation.password.min'),
            'password.confirmed' => __('validation.password.confirmed'),
    
            'identification_number.required' => __('validation.identification_number.required'),
            'identification_number.string' => __('validation.identification_number.string'),
            'identification_number.max' => __('validation.identification_number.max'),
            'identification_number.unique' => __('validation.identification_number.unique'),
    
            'nation_id.required' => __('validation.nation_id.required'),
            'nation_id.exists' => __('validation.nation_id.exists'),
    
            'phone.numeric' => __('validation.phone.numeric'),
            'phone.max' => __('validation.phone.max'),
    
            'address.string' => __('validation.address.string'),
            'address.max' => __('validation.address.max'),
    
            'avatar.string' => __('validation.avatar.string'),
        ];
    }    
}
