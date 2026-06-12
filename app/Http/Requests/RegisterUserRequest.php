<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username'     => 'required|string|max:100|min:3 ',
            'email'    => 'required|string|email|max:255|unique:usuarios,correo',
            'telefono' => 'required|nullable|digits_between:7,20',
            'password' => 'required|string|min:8|max:20',
            'confirmPassword' => 'required|string|min:8|max:20|same:password',
        ];
    }

        public function messages(): array
        {
            return [
                'username.required' => 'El nombre de usuario es obligatorio.',
                'username.max'      => 'El nombre de usuario no puede tener más de 100 caracteres.',
                'username.min'      => 'El nombre de usuario debe tener al menos 3 caracteres.',

                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email'    => 'El correo electrónico debe ser una dirección válida.',
                'email.unique'   => 'Este correo electrónico ya está registrado.',

                'telefono.required' => 'El número de teléfono es obligatorio.',
                'telefono.digits_between' => 'El número de teléfono debe contener entre 7 y 20 dígitos.',
                

                'password.required' => 'La contraseña es obligatoria.',
                'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
                'password.max'      => 'La contraseña no puede tener más de 20 caracteres.',
                
                'confirmPassword.required' => 'La confirmación de la contraseña es obligatoria.',
                'confirmPassword.same'     => 'La confirmación de la contraseña debe coincidir con la contraseña.',
            ];
        }



}
