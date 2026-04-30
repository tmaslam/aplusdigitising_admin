<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminSimulationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('password_hash')->nullable();
            $table->string('user_password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });
    }

    public function test_admin_can_start_customer_simulation_and_logout_back_to_admin(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->post('/v/simulate-login/2', [
            'return_to' => '/v/customer_list.php?page=2',
        ])
            ->assertRedirect('/dashboard.php');

        $this->assertSame(1, session('impersonator_admin_id'));
        $this->assertSame(2, session('customer_user_id'));
        $this->assertSame('1dollar', session('customer_site_key'));

        $this->get('/logout.php')
            ->assertRedirect('/v/customer_list.php?page=2');

        $this->assertSame(1, session('admin_user_id'));
        $this->assertNull(session('customer_user_id'));
        $this->assertNull(session('impersonator_admin_id'));
    }

    public function test_admin_simulation_falls_back_to_welcome_for_unsafe_return_path(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->post('/v/simulate-login/2', [
            'return_to' => 'https://evil.example/phish',
        ])->assertRedirect('/dashboard.php');

        $this->get('/logout.php')->assertRedirect('/welcome.php');
    }

    public function test_guest_cannot_start_simulation(): void
    {
        AdminUser::query()->create([
            'user_id' => 2,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-one',
            'user_email' => 'customer@example.com',
            'is_active' => 1,
        ]);

        $this->post('/v/simulate-login/2')
            ->assertRedirect('/v');
    }

    public function test_customer_session_cannot_start_simulation(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 3,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'team-one',
                'user_email' => 'team@example.com',
                'first_name' => null,
                'last_name' => null,
                'password_hash' => null,
                'user_password' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        $this->withSession([
            'customer_user_id' => 2,
            'customer_user_name' => 'Customer One',
            'customer_site_key' => '1dollar',
        ])->post('/v/simulate-login/3')
            ->assertRedirect('/v');
    }

    public function test_admin_login_clears_customer_and_impersonation_session_state(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 10,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'fresh-admin',
                'user_email' => 'fresh-admin@example.com',
                'user_password' => 'secret123',
                'first_name' => null,
                'last_name' => null,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        $this->withSession([
            'customer_user_id' => 2,
            'customer_user_name' => 'Customer One',
            'customer_site_key' => '1dollar',
            'impersonator_admin_id' => 1,
            'impersonator_admin_name' => 'main-admin',
            'impersonation_target_user_id' => 2,
            'impersonation_target_role' => 'customer',
            'impersonation_target_name' => 'Customer One',
            'impersonator_return_path' => '/v/customer_list.php',
        ])->post('/v/login', [
            'txtLogin' => 'fresh-admin',
            'txtPassword' => 'secret123',
        ])->assertRedirect('/v/login-2fa');

        $this->assertSame(10, session('admin_pending_2fa_user_id'));
        $this->assertNull(session('admin_user_id'));
        $this->assertNull(session('customer_user_id'));
        $this->assertNull(session('customer_user_name'));
        $this->assertNull(session('customer_site_key'));
        $this->assertNull(session('impersonator_admin_id'));
        $this->assertNull(session('impersonation_target_user_id'));
    }
}
