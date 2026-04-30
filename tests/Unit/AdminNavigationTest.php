<?php

namespace Tests\Unit;

use App\Models\AdminUser;
use App\Support\AdminNavigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->integer('is_active')->default(1);
            $table->string('user_name')->nullable();
            $table->string('website')->nullable();
            $table->string('user_term')->nullable();
            $table->string('exist_customer')->nullable();
            $table->string('real_user')->nullable();
            $table->string('end_date')->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('order_type')->nullable();
            $table->string('status')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date')->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved')->nullable();
            $table->string('payment')->nullable();
            $table->integer('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('end_date')->nullable();
        });

        Schema::create('quote_negotiations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function test_counts_treat_blank_and_zero_dates_as_active_for_users(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 10,
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'is_active' => 1,
                'user_name' => 'customer-blank',
                'website' => '1dollar',
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'end_date' => '',
            ],
            [
                'user_id' => 11,
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'is_active' => 1,
                'user_name' => 'team-zero-date',
                'website' => '1dollar',
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'end_date' => '0000-00-00 00:00:00',
            ],
            [
                'user_id' => 12,
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'is_active' => 0,
                'user_name' => 'blocked-existing',
                'website' => '1dollar',
                'user_term' => 'ip',
                'exist_customer' => '1',
                'real_user' => '1',
                'end_date' => '',
            ],
            [
                'user_id' => 13,
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'is_active' => 0,
                'user_name' => 'pending-approval',
                'website' => '1dollar',
                'user_term' => 'dc',
                'exist_customer' => 'pending_admin_approval',
                'real_user' => '1',
                'end_date' => '',
            ],
        ]);

        $counts = AdminNavigation::counts();

        $this->assertSame(1, $counts['customers']);
        $this->assertSame(1, $counts['teams']);
        $this->assertSame(1, $counts['blocked_customers']);
        $this->assertSame(1, $counts['pending_customer_approvals']);
    }

    public function test_quote_negotiation_count_ignores_soft_deleted_quote_orders(): void
    {
        DB::table('orders')->insert([
            [
                'order_id' => 9001,
                'assign_to' => 0,
                'order_type' => 'quote',
                'status' => 'Underprocess',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'order_id' => 9002,
                'assign_to' => 0,
                'order_type' => 'vector',
                'status' => 'Underprocess',
                'is_active' => 1,
                'end_date' => '2026-04-06 15:04:00',
            ],
        ]);

        DB::table('quote_negotiations')->insert([
            [
                'order_id' => 9001,
                'customer_user_id' => 101,
                'status' => 'pending_admin_review',
            ],
            [
                'order_id' => 9002,
                'customer_user_id' => 102,
                'status' => 'pending_admin_review',
            ],
        ]);

        $counts = AdminNavigation::counts();

        $this->assertSame(1, $counts['quote_negotiations']);
    }

    public function test_converted_quote_moves_from_quote_counts_to_order_counts(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9010,
            'assign_to' => 0,
            'order_type' => 'quote',
            'status' => 'Underprocess',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $before = AdminNavigation::counts();

        $this->assertSame(1, $before['new_quotes']);
        $this->assertSame(0, $before['new_orders']);

        DB::table('orders')->where('order_id', 9010)->update([
            'order_type' => 'order',
            'status' => 'Underprocess',
            'assign_to' => 0,
        ]);

        $after = AdminNavigation::counts();

        $this->assertSame(0, $after['new_quotes']);
        $this->assertSame(1, $after['new_orders']);
    }
}
