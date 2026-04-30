<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamQueueRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_comments');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
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
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('design_name')->nullable();
            $table->string('turn_around_time', 30)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('working', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date')->nullable();
            $table->string('file_name_with_order_id')->nullable();
            $table->string('file_source', 50)->nullable();
            $table->string('date_added')->nullable();
        });

        Schema::create('order_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->text('comments')->nullable();
            $table->string('source_page', 50)->nullable();
            $table->string('comment_source', 50)->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('date_modified', 30)->nullable();
        });

        DB::table('users')->insert([
            [
                'user_id' => 10,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'digitizer-one',
                'user_email' => 'digitizer-one@example.com',
                'first_name' => 'Digitizer',
                'last_name' => 'One',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 101,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 777,
                'user_id' => 101,
                'assign_to' => 10,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'Underprocess',
                'design_name' => 'Queue Test Order',
                'turn_around_time' => 'Standard',
                'completion_date' => now()->addHours(6)->format('Y-m-d H:i:s'),
                'working' => null,
                'end_date' => null,
            ],
            [
                'order_id' => 778,
                'user_id' => 101,
                'assign_to' => 10,
                'website' => '1dollar',
                'order_type' => 'qquote',
                'status' => 'Underprocess',
                'design_name' => 'Queue Test Quick Quote',
                'turn_around_time' => 'Standard',
                'completion_date' => now()->addHours(4)->format('Y-m-d H:i:s'),
                'working' => null,
                'end_date' => null,
            ],
        ]);
    }

    public function test_modern_team_queue_route_renders_orders_page_with_stable_detail_link(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/queues/new-orders');

        $response->assertOk();
        $response->assertSee('New Orders');
        $response->assertSee('101-777');
        $response->assertSee('/team/orders/777/detail/order?queue=new-orders', false);
    }

    public function test_legacy_working_queue_link_redirects_to_modern_queue_route(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/under-process-orders.php?process=working');

        $response->assertRedirect('/team/queues/working-orders');
    }

    public function test_save_working_returns_to_the_same_queue(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->post('/team/orders/777/working', [
            'queue' => 'working-orders',
        ]);

        $response->assertRedirect('/team/queues/working-orders');
        $this->assertNotNull(DB::table('orders')->where('order_id', 777)->value('working'));
    }

    public function test_quick_quote_queue_renders_stable_detail_link(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/queues/quick-quotes');

        $response->assertOk();
        $response->assertSee('Quick Quotes');
        $response->assertSee('101-778');
        $response->assertSee('/team/quick-quotes/778/detail', false);
    }

    public function test_team_queue_can_export_csv(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/queues/new-orders?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Order ID', $response->streamedContent());
        $this->assertStringContainsString('101-777', $response->streamedContent());
    }
}
