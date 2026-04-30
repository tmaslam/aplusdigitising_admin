<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerIsolationTest extends TestCase
{
    private string $uploadsRoot;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.primary_legacy_key', '1dollar');
        $this->uploadsRoot = '/tmp/unified-phase2-isolation-'.uniqid();
        config()->set('app.shared_uploads_path', $this->uploadsRoot);

        Schema::disableForeignKeyConstraints();
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

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date');
            $table->string('file_source', 50);
            $table->string('date_added')->nullable();
        });

        \App\Models\AdminUser::query()->insert([
            [
                'user_id' => 100,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer1@example.com',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 101,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-two',
                'user_email' => 'customer2@example.com',
                'is_active' => 1,
                'end_date' => null,
            ],
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

        \App\Models\Attachment::query()->create([
            'id' => 400,
            'order_id' => 200,
            'file_name' => 'customer-one-preview.pdf',
            'file_name_with_date' => 'customer-one-preview.pdf',
            'file_source' => 'scanned',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        $directory = $this->uploadsRoot.DIRECTORY_SEPARATOR.'scanned';
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($directory.DIRECTORY_SEPARATOR.'customer-one-preview.pdf', '%PDF-1.4 test');
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        $this->deleteDirectory($this->uploadsRoot);

        parent::tearDown();
    }

    public function test_customer_cannot_view_another_customers_order_detail(): void
    {
        $this->withSession([
            'customer_user_id' => 101,
            'customer_user_name' => 'customer-two',
            'customer_site_key' => '1dollar',
        ])->get('/view-order-detail.php?order_id=200')->assertNotFound();
    }

    public function test_customer_cannot_download_another_customers_attachment(): void
    {
        $this->withSession([
            'customer_user_id' => 101,
            'customer_user_name' => 'customer-two',
            'customer_site_key' => '1dollar',
        ])->get('/download.php?attachment_id=400')->assertStatus(403);
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
