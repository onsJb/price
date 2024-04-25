<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'phone_number' => 'required|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ];
    }
}
