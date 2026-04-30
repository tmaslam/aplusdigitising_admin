<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\PasswordManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerLegacyPasswordLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('login_history');
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

        Schema::create('login_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('IP_Address', 45);
            $table->string('Login_Name')->nullable();
            $table->string('Password')->nullable();
            $table->string('Status')->nullable();
            $table->string('Date_Added')->nullable();
        });

        PasswordManager::refreshColumnAvailability();
    }

    public function test_customer_login_upgrades_legacy_plain_text_password_to_secure_hash(): void
    {
        AdminUser::query()->create([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'legacy-customer',
            'user_email' => 'legacy@example.com',
            'user_password' => 'secret123',
            'password_hash' => null,
            'is_active' => 1,
            'exist_customer' => '1',
        ]);

        $this->post('/login.php', [
            'user_id' => 'legacy@example.com',
            'user_psw' => 'secret123',
        ])->assertRedirect('/dashboard.php');

        $user = AdminUser::query()->findOrFail(100);

        $this->assertSame('', (string) $user->user_password);
        $this->assertNotSame('', trim((string) $user->password_hash));
        $this->assertNotNull($user->password_migrated_at);
    }
}
