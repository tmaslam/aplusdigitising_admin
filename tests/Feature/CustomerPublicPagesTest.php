<?php

namespace Tests\Feature;

use App\Support\SiteContext;
use Tests\TestCase;

class CustomerPublicPagesTest extends TestCase
{
    public function test_public_pages_render_successfully(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Flawless Custom Embroidery Digitizing &amp; Vector Art Services', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('application/ld+json', false);
        $this->get('/home.php')->assertOk();
        $this->get('/index-new.php')->assertOk();
        $this->get('/work-process.php')->assertOk();
        $this->get('/price-plan.php')->assertOk();
        $this->get('/embroidery-digitizing.php')
            ->assertOk()
            ->assertSee('"@type":"Service"', false);
        $this->get('/payment-options.php')->assertRedirect('/contact-us.php');
        $this->get('/instant-payment.php')->assertRedirect('/login.php');
        $this->get('/instant-payment-paypal.php')->assertRedirect('/login.php');
        $this->get('/contact-us.php')->assertOk();
        $this->get('/login.php')->assertOk();
        $this->get('/sign-up.php')->assertOk();
        $this->get('/forget-password.php')->assertOk();
    }

    public function test_work_process_page_uses_responsive_step_grid_markup(): void
    {
        $this->get('/work-process.php')
            ->assertOk()
            ->assertSee('timeline-step-grid work-process-step-grid', false)
            ->assertDontSee('grid-template-columns:repeat(4, minmax(0, 1fr));', false);
    }

    public function test_public_seo_endpoints_render_successfully(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: http://localhost/sitemap.xml', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<urlset', false)
            ->assertSee('http://localhost/price-plan.php', false)
            ->assertSee('http://localhost/contact-us.php', false);
    }

    public function test_customer_only_aliases_redirect_guests_to_login(): void
    {
        $this->get('/digitizing-quote.php')->assertRedirect('/login.php');
        $this->get('/vector-quote.php')->assertRedirect('/login.php');
        $this->get('/edit-quote.php?order_id=1')->assertRedirect('/login.php');
        $this->get('/payment.php')->assertRedirect('/login.php');
        $this->get('/refund-apply.php')->assertRedirect('/login.php');
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $response = $this->post('/contact-us.php', [
            'name' => 'Test Customer',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'subject', 'message']);
    }

    public function test_contact_email_body_includes_sender_ip_address(): void
    {
        $html = view('customer.emails.contact-message', [
            'siteContext' => new SiteContext(
                1,
                '1dollar',
                '1dollar',
                'APlus',
                'A Plus Digitizing',
                'localhost',
                'support@example.com',
                'support@example.com',
                'http://localhost',
                true
            ),
            'payload' => [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'company' => 'Test Co',
                'phone' => '555-0100',
                'subject' => 'Need help',
                'message' => 'Please contact me.',
                'ip_address' => '203.0.113.10',
            ],
        ])->render();

        $this->assertStringContainsString('IP Address:</strong> 203.0.113.10', $html);
    }
}
