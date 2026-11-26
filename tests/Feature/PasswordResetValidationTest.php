<?php

namespace Tests\Feature;

use Tests\TestCase;

class PasswordResetValidationTest extends TestCase
{
    public function test_reset_rejects_passwords_that_do_not_meet_registration_rules(): void
    {
        foreach ([
            '12345678',
            'Abcdefg!',
            'Abc12345',
            'Abc12345!Abc12345!Abc',
        ] as $password) {
            $response = $this->post(route('password.update'), [
                'token' => 'test-token',
                'email' => 'usuario@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

            $response->assertSessionHasErrors('password');
        }
    }
}
