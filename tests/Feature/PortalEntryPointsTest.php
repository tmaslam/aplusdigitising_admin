<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortalEntryPointsTest extends TestCase
{
    public function test_public_portal_entry_points_render_or_redirect_as_expected(): void
    {
        $this->get('/portal')->assertOk();
        $this->get('/portal/index.php')->assertOk();

        $this->get('/admin')->assertOk();
        $this->get('/admin/login')->assertOk();
        $this->get('/v')->assertOk();
        $this->get('/v/index.php')->assertOk();

        $this->get('/team')->assertOk();
        $this->get('/team/index.php')->assertOk();
    }

    public function test_protected_portal_dashboards_redirect_guests_to_their_login_entry_points(): void
    {
        $this->get('/v/welcome.php')->assertRedirect('/v');
        $this->get('/team/welcome.php')->assertRedirect('/team');
        $this->get('/dashboard.php')->assertRedirect('/login.php');
    }
}
