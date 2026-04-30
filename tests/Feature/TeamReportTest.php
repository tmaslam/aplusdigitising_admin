<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('comments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('users');

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
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('design_name', 255)->nullable();
            $table->string('stitches', 255)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('comment_source', 60)->nullable();
            $table->string('source_page', 60)->nullable();
            $table->text('comments')->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('date_modified', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('is_paid')->default(0);
            $table->string('end_date', 30)->nullable();
        });

        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'admin',
                'user_email' => 'admin@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'digitizer-one',
                'user_email' => 'team@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 3,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_SUPERVISOR,
                'user_name' => 'supervisor-one',
                'user_email' => 'supervisor@example.com',
                'is_active' => 1,
            ],
        ]);

        \DB::table('orders')->insert([
            [
                'order_id' => 901,
                'user_id' => 10,
                'assign_to' => 2,
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Logo A',
                'stitches' => '4500',
                'total_amount' => '5.00',
                'completion_date' => '2026-03-28 10:00:00',
                'end_date' => null,
            ],
            [
                'order_id' => 902,
                'user_id' => 11,
                'assign_to' => 2,
                'order_type' => 'vector',
                'status' => 'approved',
                'design_name' => 'Vector B',
                'stitches' => '2:00',
                'total_amount' => '12.00',
                'completion_date' => '2026-03-29 11:00:00',
                'end_date' => null,
            ],
            [
                'order_id' => 903,
                'user_id' => 12,
                'assign_to' => 3,
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Supervisor Logo',
                'stitches' => '6200',
                'total_amount' => '9.00',
                'completion_date' => '2026-03-30 09:30:00',
                'end_date' => null,
            ],
        ]);

        \DB::table('comments')->insert([
            'order_id' => 901,
            'comment_source' => 'supervisorReview',
            'source_page' => 'supervisorReview',
            'comments' => 'Verified by supervisor',
            'date_added' => '2026-03-28 12:00:00',
            'date_modified' => '2026-03-28 12:00:00',
        ]);
    }

    public function test_team_report_shows_design_type_and_supervisor_checked_summary(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/monthly-reports.php?team=2&month=2026-03');

        $response->assertOk();
        $response->assertSee('Design Type');
        $response->assertSee('Supervisor Checked');
        $response->assertSee('Digitizing');
        $response->assertSee('Vector');
        $response->assertSee('Yes');
    }

    public function test_team_report_can_export_csv(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/monthly-reports.php?team=2&month=2026-03&export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('"Order ID","Design Type","Design Name","Assigned To","Supervisor Checked",Stitches,"Total Amount","Completion Date"', $content);
        $this->assertStringContainsString('901,Digitizing,"Logo A",digitizer-one,Yes,4500,5.00,"2026-03-28 10:00:00"', $content);
    }

    public function test_team_report_includes_supervisors_in_selector_and_results(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/monthly-reports.php?team=3&month=2026-03');

        $response->assertOk();
        $response->assertSee('supervisor-one (Supervisor)');
        $response->assertSee('Supervisor Logo');
        $response->assertSee('9.00');
    }

    public function test_team_report_month_filter_excludes_other_months_and_soft_deleted_orders(): void
    {
        \DB::table('orders')->insert([
            [
                'order_id' => 904,
                'user_id' => 13,
                'assign_to' => 2,
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Older Month Order',
                'stitches' => '4000',
                'total_amount' => '6.00',
                'completion_date' => '2026-02-26 10:00:00',
                'end_date' => null,
            ],
            [
                'order_id' => 905,
                'user_id' => 14,
                'assign_to' => 2,
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Soft Deleted Order',
                'stitches' => '4100',
                'total_amount' => '7.00',
                'completion_date' => '2026-03-15 10:00:00',
                'end_date' => '2026-03-16 10:00:00',
            ],
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/monthly-reports.php?team=2&month=2026-03');

        $response->assertOk();
        $response->assertSee('Logo A');
        $response->assertSee('Vector B');
        $response->assertDontSee('Older Month Order');
        $response->assertDontSee('Soft Deleted Order');
    }
}
