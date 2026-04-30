<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminQuickQuoteCompletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('qucik_quote_users');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('website', 30)->nullable();
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
            $table->string('website', 30)->nullable();
            $table->string('stitches', 255)->nullable();
            $table->string('stitches_price', 255)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('design_name', 255)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('qucik_quote_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_oid');
            $table->string('customer_email')->nullable();
        });

        \App\Models\AdminUser::query()->insert([
            [
                'user_id' => 1,
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'website' => '1dollar',
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 2,
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'website' => '1dollar',
                'user_name' => 'team-user',
                'user_email' => 'team@example.com',
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('qucik_quote_users');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_admin_can_complete_team_assigned_quick_quote_from_admin_screen(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 501,
            'user_id' => 1,
            'assign_to' => 2,
            'order_type' => 'qquote',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'design_name' => 'Quick Quote Design',
            'end_date' => null,
        ]);

        \DB::table('qucik_quote_users')->insert([
            'customer_oid' => 501,
            'customer_email' => null,
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/quick-order/complete', [
            'order_id' => 501,
            'stitches' => '4200',
            'stamount' => '11.00',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 501,
            'status' => 'done',
            'stitches' => '4200',
            'stitches_price' => '11.00',
            'total_amount' => '11.00',
        ]);
    }
}
