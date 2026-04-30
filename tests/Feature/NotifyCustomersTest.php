<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotifyCustomersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('template_name', 150);
            $table->string('subject', 255);
            $table->mediumText('body');
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 150)->nullable();
            $table->string('updated_by', 150)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 30)->unique();
            $table->string('name', 150)->nullable();
            $table->string('brand_name', 150)->nullable();
            $table->integer('is_primary')->default(0);
            $table->integer('is_active')->default(1);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('is_paid')->default(0);
            $table->string('end_date', 30)->nullable();
        });

        \DB::table('sites')->insert([
            ['id' => 1, 'legacy_key' => '1dollar', 'name' => 'APlus', 'brand_name' => 'A Plus Digitizing', 'is_primary' => 1, 'is_active' => 1],
            ['id' => 2, 'legacy_key' => 'site2', 'name' => 'Site 2', 'brand_name' => 'Site Two', 'is_primary' => 0, 'is_active' => 1],
        ]);

        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 11,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'alpha',
                'user_email' => 'alpha@example.com',
                'first_name' => 'Alpha',
                'last_name' => 'One',
                'is_active' => 1,
            ],
            [
                'user_id' => 22,
                'website' => 'site2',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'bravo',
                'user_email' => 'bravo@example.com',
                'first_name' => 'Bravo',
                'last_name' => 'Two',
                'is_active' => 1,
            ],
        ]);
    }

    public function test_notify_customers_filters_to_selected_site_and_hides_external_email_field(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/notify-customers.php?website=1dollar');

        $response->assertOk();
        $response->assertSee('alpha@example.com');
        $response->assertDontSee('bravo@example.com');
        $response->assertDontSee('external_emails');
    }

    public function test_notify_customers_search_filters_by_email_or_user_id(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/notify-customers.php?website=1dollar&search=alpha@example.com');

        $response->assertOk();
        $response->assertSee('alpha@example.com');
        $response->assertDontSee('bravo@example.com');
    }

    public function test_notify_customers_send_rejects_cross_site_only_selection(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Site notice',
                'body' => 'Hello customers',
                'recipients' => [22],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHasErrors('recipients');
    }

    public function test_notify_customers_send_accepts_selected_customers_on_active_site(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Site notice',
                'body' => 'Hello customers',
                'recipients' => [11, 22],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHas('success');
    }

    public function test_notify_customers_wraps_plain_body_in_standard_email_layout(): void
    {
        Mail::spy();

        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Site notice',
                'body' => 'Hello customers',
                'recipients' => [11],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHas('success');

        $this->assertNotifyCustomerMailHtml('alpha@example.com', function (string $html): void {
            $this->assertStringContainsString('font-family:Arial,Helvetica,sans-serif', $html);
            $this->assertStringContainsString('A Plus Digitizing', $html);
            $this->assertStringContainsString('Hello customers', $html);
        });
    }

    public function test_html_template_body_is_not_double_escaped_when_template_id_is_provided(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        \DB::table('email_templates')->insert([
            'id' => 1,
            'site_id' => 1,
            'template_name' => 'HTML Promo',
            'subject' => 'Special Offer',
            'body' => '<h1>Hello</h1><p>Welcome &amp; enjoy</p>',
            'is_active' => 1,
            'created_by' => 'admin',
            'updated_by' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Mail::spy();

        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Special Offer',
                'body' => '<h1>Hello</h1><p>Welcome &amp; enjoy</p>',
                'template_id' => '1',
                'recipients' => [11],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHas('success');

        $this->assertNotifyCustomerMailHtml('alpha@example.com', function (string $html): void {
            $this->assertStringContainsString('<h1>Hello</h1><p>Welcome &amp; enjoy</p>', $html);
            $this->assertStringNotContainsString('&lt;h1&gt;Hello&lt;/h1&gt;', $html);
            $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif !important;', $html);
        });
    }

    public function test_notify_customers_forces_professional_font_for_template_markup(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        \DB::table('email_templates')->insert([
            'id' => 2,
            'site_id' => 1,
            'template_name' => 'HTML Generic Notice',
            'subject' => 'Generic Notification',
            'body' => 'Hello,<div>I am sending generic notification.</div><div><span style="font-family: monospace; background-color: rgba(255, 255, 255, 0.62);">{{site_name}}</span></div>',
            'is_active' => 1,
            'created_by' => 'admin',
            'updated_by' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Mail::spy();

        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Generic Notification',
                'body' => 'Hello,<div>I am sending generic notification.</div><div><span style="font-family: monospace; background-color: rgba(255, 255, 255, 0.62);">{{site_name}}</span></div>',
                'template_id' => '2',
                'recipients' => [11],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHas('success');

        $this->assertNotifyCustomerMailHtml('alpha@example.com', function (string $html): void {
            $this->assertStringContainsString('<span style="font-family: monospace; background-color: rgba(255, 255, 255, 0.62);">{{site_name}}</span>', $html);
            $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif !important;', $html);
        });
    }

    public function test_notify_customers_preserves_white_text_for_button_style_links(): void
    {
        Mail::spy();

        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/notify-customers.php?website=1dollar')
            ->post('/v/notify-customers.php', [
                'website' => '1dollar',
                'subject' => 'Site notice',
                'body' => '<p><a href="https://example.com" class="button-link" style="display:inline-block;padding:12px 20px;background:#17212a;color:#ffffff !important;text-decoration:none;font-weight:700;">Open Notice</a></p>',
                'recipients' => [11],
            ]);

        $response->assertRedirect('/v/notify-customers.php?website=1dollar');
        $response->assertSessionHas('success');

        $this->assertNotifyCustomerMailHtml('alpha@example.com', function (string $html): void {
            $this->assertStringContainsString('.button-link {', $html);
            $this->assertStringContainsString('color: #ffffff !important;', $html);
            $this->assertStringContainsString('background:#17212a;color:#ffffff !important', $html);
        });
    }

    private function assertNotifyCustomerMailHtml(string $recipient, callable $assertions): void
    {
        Mail::shouldHaveReceived('send')
            ->withArgs(function ($view, $data, $callback) use ($recipient, $assertions) {
                if ($view !== [] || $data !== [] || ! is_callable($callback)) {
                    return false;
                }

                $message = new class
                {
                    public array $to = [];
                    public string $subject = '';
                    public string $html = '';

                    public function to($recipients)
                    {
                        $this->to = array_map('strtolower', (array) $recipients);

                        return $this;
                    }

                    public function subject($subject)
                    {
                        $this->subject = (string) $subject;

                        return $this;
                    }

                    public function html($html)
                    {
                        $this->html = (string) $html;

                        return $this;
                    }

                    public function from(...$arguments)
                    {
                        return $this;
                    }

                    public function replyTo(...$arguments)
                    {
                        return $this;
                    }

                    public function sender(...$arguments)
                    {
                        return $this;
                    }
                };

                $callback($message);

                if (! in_array(strtolower($recipient), $message->to, true)) {
                    return false;
                }

                $assertions($message->html);

                return true;
            })
            ->once();
    }
}
