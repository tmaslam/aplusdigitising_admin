<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\PasswordManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerLoginBoundaryTest extends TestCase
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
            $table->string('alternate_email')->nullable();
            $table->string('user_password')->nullable();
            $table->string('password_hash')->nullable();
            $table->dateTime('password_migrated_at')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('exist_customer')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        PasswordManager::refreshColumnAvailability();
    }

    public function test_inactive_customer_cannot_log_in_before_verification(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'inactive-customer',
            'user_email' => 'inactive@example.com',
            'is_active' => 0,
            'exist_customer' => '0',
        ], PasswordManager::payload('secret123')));

        $this->post('/login.php', [
            'user_id' => 'inactive@example.com',
            'user_psw' => 'secret123',
        ])->assertSessionHasErrors('auth');
    }

    public function test_customer_from_another_site_cannot_log_in_on_primary_site(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 101,
            'website' => 'brandb',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'brandb-customer',
            'user_email' => 'brandb@example.com',
            'is_active' => 1,
            'exist_customer' => '1',
        ], PasswordManager::payload('secret123')));

        $this->post('/login.php', [
            'user_id' => 'brandb@example.com',
            'user_psw' => 'secret123',
        ])->assertSessionHasErrors('auth');
    }

    public function test_login_prefers_active_customer_when_deleted_duplicate_shares_email(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 1164,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'drosen@rrind.com',
            'user_email' => 'eduardo@rrind.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'end_date' => null,
        ], PasswordManager::payload('eduardo123rrind!')));

        AdminUser::query()->create(array_merge([
            'user_id' => 1196,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'salesupport@rrind.com',
            'user_email' => 'eduardo@rrind.com',
            'alternate_email' => 'salesupport@rrind.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'end_date' => '2026-03-24 19:43:59',
        ], PasswordManager::payload('other-password')));

        $this->post('/login.php', [
            'user_id' => 'eduardo@rrind.com',
            'user_psw' => 'eduardo123rrind!',
        ])->assertRedirect('/dashboard.php');

        $this->assertSame(1164, session('customer_user_id'));
    }

    public function test_password_reset_lookup_prefers_active_customer_when_deleted_duplicate_shares_email(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 1164,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'drosen@rrind.com',
            'user_email' => 'eduardo@rrind.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'end_date' => null,
        ], PasswordManager::payload('eduardo123rrind!')));

        AdminUser::query()->create(array_merge([
            'user_id' => 1196,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'salesupport@rrind.com',
            'user_email' => 'eduardo@rrind.com',
            'alternate_email' => 'salesupport@rrind.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'end_date' => '2026-03-24 19:43:59',
        ], PasswordManager::payload('other-password')));

        $customer = AdminUser::query()
            ->customers()
            ->active()
            ->forWebsite('1dollar')
            ->where(function ($query) {
                $query->where('user_email', 'eduardo@rrind.com')
                    ->orWhere('alternate_email', 'eduardo@rrind.com')
                    ->orWhere('user_name', 'eduardo@rrind.com');
            })
            ->orderByRaw("
                CASE
                    WHEN end_date IS NULL
                        OR end_date = ''
                        OR end_date = '0000-00-00'
                        OR end_date = '0000-00-00 00:00:00'
                    THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('user_id')
            ->first();

        $this->assertNotNull($customer);
        $this->assertSame(1164, $customer->user_id);
    }
}
