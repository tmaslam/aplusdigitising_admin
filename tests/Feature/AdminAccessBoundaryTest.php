<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAccessBoundaryTest extends TestCase
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

        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 3,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'team-one',
                'user_email' => 'team@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 4,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_SUPERVISOR,
                'user_name' => 'supervisor-one',
                'user_email' => 'supervisor@example.com',
                'is_active' => 1,
            ],
        ]);
    }

    public function test_guest_cannot_open_admin_dashboard_or_tools(): void
    {
        $this->get('/v/welcome.php')->assertRedirect('/v');
        $this->get('/v/payment-due-report.php')->assertRedirect('/v');
    }

    public function test_customer_session_cannot_open_admin_routes(): void
    {
        $this->withSession([
            'customer_user_id' => 2,
            'customer_user_name' => 'Customer One',
            'customer_site_key' => '1dollar',
        ])->get('/v/welcome.php')->assertRedirect('/v');

        $this->withSession([
            'customer_user_id' => 2,
            'customer_user_name' => 'Customer One',
            'customer_site_key' => '1dollar',
        ])->get('/v/customer_list.php')->assertRedirect('/v');
    }

    public function test_team_and_supervisor_sessions_cannot_open_admin_routes(): void
    {
        $this->withSession([
            'team_user_id' => 3,
            'team_user_name' => 'team-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/v/welcome.php')->assertRedirect('/v');

        $this->withSession([
            'team_user_id' => 4,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ])->get('/v/security-events.php')->assertRedirect('/v');
    }

    public function test_admin_logout_returns_to_admin_login_screen(): void
    {
        $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/logout.php')->assertRedirect('/v');
    }
}
