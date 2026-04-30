<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamSupervisorWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('supervisor_team_members');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('order_workflow_meta');
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
            $table->string('register_by')->nullable();
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
            $table->string('format')->nullable();
            $table->string('fabric_type')->nullable();
            $table->string('sew_out')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('measurement')->nullable();
            $table->string('no_of_colors')->nullable();
            $table->string('color_names')->nullable();
            $table->string('appliques')->nullable();
            $table->string('no_of_appliques')->nullable();
            $table->string('applique_colors')->nullable();
            $table->string('turn_around_time', 30)->nullable();
            $table->string('working')->nullable();
            $table->string('stitches')->nullable();
            $table->decimal('stitches_price', 12, 2)->default(0);
            $table->string('assigned_date')->nullable();
            $table->string('vender_complete_date')->nullable();
            $table->string('completion_date')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->text('comments')->nullable();
            $table->string('source_page', 50)->nullable();
            $table->string('comment_source', 50)->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('date_modified', 30)->nullable();
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('file_source', 50)->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date')->nullable();
            $table->string('file_name_with_order_id')->nullable();
            $table->string('date_added', 30)->nullable();
        });

        Schema::create('order_workflow_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('created_source', 50)->nullable();
            $table->unsignedTinyInteger('historical_backfill')->default(0);
            $table->unsignedTinyInteger('suppress_customer_notifications')->default(0);
            $table->string('delivery_override', 20)->nullable();
            $table->decimal('order_credit_limit', 12, 2)->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('date_modified', 30)->nullable();
            $table->string('end_date', 30)->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('supervisor_team_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supervisor_user_id');
            $table->unsignedBigInteger('member_user_id');
            $table->string('date_added')->nullable();
            $table->string('end_date', 30)->nullable();
            $table->string('deleted_by')->nullable();
        });

        DB::table('users')->insert([
            [
                'user_id' => 20,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_SUPERVISOR,
                'user_name' => 'supervisor-one',
                'user_email' => 'supervisor@example.com',
                'first_name' => 'Supervisor',
                'last_name' => 'One',
                'register_by' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 21,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'digitizer-one',
                'user_email' => 'digitizer@example.com',
                'first_name' => 'Digitizer',
                'last_name' => 'One',
                'register_by' => 'supervisor-one',
                'is_active' => 1,
            ],
            [
                'user_id' => 101,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'register_by' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('supervisor_team_members')->insert([
            'supervisor_user_id' => 20,
            'member_user_id' => 21,
            'date_added' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
            'deleted_by' => null,
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 880,
                'user_id' => 101,
                'assign_to' => 21,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'Ready',
                'design_name' => 'Supervisor Review Order',
                'format' => 'DST',
                'fabric_type' => 'Twill',
                'sew_out' => 'no',
                'width' => '4',
                'height' => '3',
                'measurement' => 'inch',
                'no_of_colors' => '4',
                'color_names' => 'Red, White, Blue',
                'appliques' => 'yes',
                'no_of_appliques' => '2',
                'applique_colors' => 'Gold, Black',
                'turn_around_time' => 'Standard',
                'working' => '',
                'stitches' => null,
                'stitches_price' => 0,
                'assigned_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'vender_complete_date' => now()->format('Y-m-d H:i:s'),
                'completion_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'end_date' => null,
            ],
            [
                'order_id' => 882,
                'user_id' => 101,
                'assign_to' => 20,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'Underprocess',
                'design_name' => 'Supervisor Assigned Working Order',
                'format' => 'DST',
                'fabric_type' => 'Twill',
                'sew_out' => 'no',
                'width' => '4',
                'height' => '3',
                'measurement' => 'inch',
                'no_of_colors' => '4',
                'color_names' => 'Red, White, Blue',
                'appliques' => 'yes',
                'no_of_appliques' => '2',
                'applique_colors' => 'Gold, Black',
                'turn_around_time' => 'Standard',
                'working' => '09:30',
                'stitches' => null,
                'stitches_price' => 0,
                'assigned_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'vender_complete_date' => null,
                'completion_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'end_date' => null,
            ],
        ]);

        DB::table('attach_files')->insert([
            'order_id' => 882,
            'file_source' => 'team',
            'file_name' => 'completed.dst',
            'file_name_with_date' => 'completed.dst',
            'file_name_with_order_id' => '(882) completed.dst',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_supervisor_review_queue_lists_ready_team_work(): void
    {
        $response = $this->withSession([
            'team_user_id' => 20,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ])->get('/team/review-queue.php');

        $response->assertOk();
        $response->assertSee('Review Queue');
        $response->assertSee('Supervisor Review Order');
        $response->assertSee('digitizer-one');
        $response->assertSee('Mark Reviewed');
    }

    public function test_supervisor_can_mark_ready_order_reviewed(): void
    {
        $response = $this->withSession([
            'team_user_id' => 20,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ])->post('/team/review-order.php', [
            'order_id' => 880,
            'review_note' => 'Ready for admin review.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Supervisor review saved successfully.');

        $this->assertDatabaseHas('comments', [
            'order_id' => 880,
            'comment_source' => 'supervisorReview',
            'source_page' => 'supervisorReview',
            'comments' => 'Ready for admin review.',
        ]);
    }

    public function test_team_disapproved_queue_includes_disapproved_status(): void
    {
        DB::table('orders')->insert([
            'order_id' => 881,
            'user_id' => 101,
            'assign_to' => 21,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'disapproved',
            'design_name' => 'Returned For Changes',
            'working' => '',
            'assigned_date' => now()->subHour()->format('Y-m-d H:i:s'),
            'completion_date' => now()->addHours(1)->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        $response = $this->withSession([
            'team_user_id' => 21,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/queues/disapproved-orders');

        $response->assertOk();
        $response->assertSee('Disapproved Orders');
        $response->assertSee('101-881');
        $response->assertDontSee('No records found.');
    }

    public function test_supervisor_can_complete_assigned_working_order(): void
    {
        $response = $this->withSession([
            'team_user_id' => 20,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ])->post('/team/order-detail/complete', [
            'order_id' => 882,
            'mode' => 'order',
            'queue' => 'working-orders',
            'stitches' => '8000',
        ]);

        $response->assertRedirect('/team/queues/working-orders');
        $response->assertSessionHas('success', 'You have successfully completed the order.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 882,
            'status' => 'Ready',
            'stitches' => '8000',
        ]);
    }

    public function test_team_order_text_download_includes_applique_colors(): void
    {
        $response = $this->withSession([
            'team_user_id' => 21,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/team_get_design_info_file.php?design_id=880');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('Applique Colors: Gold, Black', $response->streamedContent());
    }

    public function test_team_order_detail_hides_price_for_quote_converted_orders(): void
    {
        DB::table('order_workflow_meta')->insert([
            'order_id' => 882,
            'created_source' => 'customer_quote_conversion',
            'historical_backfill' => 0,
            'suppress_customer_notifications' => 0,
            'delivery_override' => 'auto',
            'order_credit_limit' => null,
            'created_by_user_id' => null,
            'created_by_name' => null,
            'date_added' => now()->format('Y-m-d H:i:s'),
            'date_modified' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
            'deleted_by' => null,
        ]);

        $response = $this->withSession([
            'team_user_id' => 20,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ])->get('/team/orders/882/detail/order');

        $response->assertOk();
        $response->assertDontSee('<th>Price</th>', false);
        $response->assertSee('Current Stitches', false);
    }
}
