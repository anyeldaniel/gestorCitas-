<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTrabajadorRequest extends FormRequest
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
            'nombre' => 'required|string|min:3|max:100',
            'email' => 'required|string|email|max:255|unique:usuarios,correo',
            'telefono' => 'required|digits_between:7,20',
            'descripcion' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|max:20|confirmed',
            'password_confirmation' => 'required|string|min:8|max:20|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.min'      => 'El nombre debe tener al menos 3 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El correo electrónico debe ser una dirección válida.',
            'email.unique'   => 'Este correo electrónico ya está registrado.',

            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.digits_between' => 'El número de teléfono debe contener entre 7 y 20 dígitos.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'      => 'La contraseña no puede tener más de 20 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña debe coincidir con la contraseña.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
        ];
    }

}