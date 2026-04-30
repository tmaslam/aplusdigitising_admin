<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerFileAccessTest extends TestCase
{
    private string $uploadsRoot;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.primary_legacy_key', '1dollar');
        $this->uploadsRoot = '/tmp/unified-phase2-uploads-'.uniqid();
        config()->set('app.shared_uploads_path', $this->uploadsRoot);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('billing');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('topup')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('total_amount')->nullable();
            $table->string('stitches_price')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 10)->nullable();
            $table->string('payment', 10)->nullable();
            $table->unsignedTinyInteger('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('approve_date')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date');
            $table->string('file_name_with_order_id')->nullable();
            $table->string('file_source', 50);
            $table->string('date_added')->nullable();
        });

        AdminUser::query()->create([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-one',
            'user_email' => 'customer@example.com',
            'customer_approval_limit' => '0',
            'single_approval_limit' => '0',
            'topup' => '0',
            'is_active' => 1,
            'end_date' => null,
        ]);

        \App\Models\Order::query()->create([
            'order_id' => 200,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'done',
            'total_amount' => '10.00',
            'stitches_price' => '10.00',
            'end_date' => null,
        ]);

        \App\Models\Billing::query()->create([
            'bill_id' => 300,
            'user_id' => 100,
            'order_id' => 200,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => 10.00,
            'approve_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        \App\Models\Attachment::query()->create([
            'id' => 400,
            'order_id' => 200,
            'file_name' => 'preview.pdf',
            'file_name_with_date' => 'preview-001.pdf',
            'file_source' => 'scanned',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        \App\Models\Attachment::query()->create([
            'id' => 401,
            'order_id' => 200,
            'file_name' => 'production.dst',
            'file_name_with_date' => 'production-001.dst',
            'file_source' => 'sewout',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->writeUpload('scanned', 'preview-001.pdf', '%PDF-1.4 test preview');
        $this->writeUpload('sewout', 'production-001.dst', 'DSTDATA');
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('billing');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        $this->deleteDirectory($this->uploadsRoot);

        parent::tearDown();
    }

    public function test_preview_safe_pdf_remains_accessible_while_unpaid_but_core_file_is_blocked(): void
    {
        $session = [
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ];

        $this->withSession($session)
            ->get('/preview.php?attachment_id=400')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->withSession($session)
            ->get('/download.php?attachment_id=400')
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->withSession($session)
            ->get('/download.php?attachment_id=401')
            ->assertStatus(403);
    }

    public function test_core_file_download_is_allowed_after_payment_is_recorded(): void
    {
        \App\Models\Billing::query()->where('bill_id', 300)->update([
            'payment' => 'yes',
            'is_paid' => 1,
        ]);

        $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/download.php?attachment_id=401')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_released_files_stay_hidden_until_order_is_completed(): void
    {
        \App\Models\Billing::query()->where('bill_id', 300)->update([
            'payment' => 'yes',
            'is_paid' => 1,
        ]);

        \App\Models\Order::query()->where('order_id', 200)->update([
            'status' => 'Underprocess',
        ]);

        $this->assertCount(0, \App\Support\CustomerAttachmentAccess::releasedAttachments(\App\Models\Order::query()->findOrFail(200)));

        $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/download.php?attachment_id=401')
            ->assertStatus(403);
    }

    public function test_credit_limits_unlock_production_files_when_order_is_within_credit_window(): void
    {
        AdminUser::query()->whereKey(100)->update([
            'customer_approval_limit' => '15.00',
            'single_approval_limit' => '10.00',
            'topup' => '0',
        ]);

        $summary = \App\Support\CustomerReleaseGate::summary(\App\Models\Order::query()->findOrFail(200));

        $this->assertTrue($summary['full_release_allowed']);

        $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/download.php?attachment_id=401')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_credit_limits_do_not_unlock_production_files_when_projected_exposure_exceeds_limit(): void
    {
        AdminUser::query()->whereKey(100)->update([
            'customer_approval_limit' => '15.00',
            'single_approval_limit' => '10.00',
            'topup' => '0',
        ]);

        \App\Models\Billing::query()->create([
            'bill_id' => 301,
            'user_id' => 100,
            'order_id' => 201,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => 10.00,
            'approve_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        $summary = \App\Support\CustomerReleaseGate::summary(\App\Models\Order::query()->findOrFail(200));

        $this->assertFalse($summary['full_release_allowed']);

        $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/download.php?attachment_id=401')
            ->assertStatus(403);
    }

    public function test_converted_order_source_attachment_falls_back_to_quotes_folder_when_order_copy_is_missing(): void
    {
        \App\Models\Attachment::query()->create([
            'id' => 402,
            'order_id' => 200,
            'file_name' => 'converted-preview.jpg',
            'file_name_with_date' => 'converted-preview-001.jpg',
            'file_name_with_order_id' => '(200) converted-preview.jpg',
            'file_source' => 'order',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->writeUpload('quotes', 'converted-preview-001.jpg', 'JPEGDATA');

        $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/preview.php?attachment_id=402')
            ->assertOk();
    }

    private function writeUpload(string $folder, string $fileName, string $contents): void
    {
        $directory = $this->uploadsRoot.DIRECTORY_SEPARATOR.$folder;

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($directory.DIRECTORY_SEPARATOR.$fileName, $contents);
    }

    private function deleteDirectory(string $path): void
    {
        if ($path === '' || ! is_dir($path)) {
            return;
        }

        $items = scandir($path);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$item;

            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } elseif (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        @rmdir($path);
    }
}
