<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    public function test_cliente_no_puede_acceder_a_rutas_de_administracion_ni_verificacion_de_pagos(): void
    {
        $this->actingAs(new User(['rol' => 'cliente']));

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/verificar-pagos')->assertForbidden();
    }

}
