<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerSecuritySurfaceTest extends TestCase
{
    public function test_forgot_password_stays_generic_when_reset_table_is_missing(): void
    {
        $response = $this->post('/forget-password.php', [
            'identity' => 'unknown@example.com',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_payment_return_without_reference_is_not_exposed(): void
    {
        $this->get('/successpay.php')->assertNotFound();
    }
}
