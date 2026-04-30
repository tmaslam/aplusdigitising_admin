<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamQuickQuoteAccessTest extends TestCase
{
    private string $uploadsRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploadsRoot = '/tmp/unified-phase2-team-uploads-'.uniqid();
        config()->set('app.shared_uploads_path', $this->uploadsRoot);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('qucik_quote_users');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
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
            $table->string('no_of_appliques')->nullable();
            $table->string('applique_colors')->nullable();
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

        Schema::create('qucik_quote_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
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

        AdminUser::query()->insert([
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
                'user_id' => 11,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'digitizer-two',
                'user_email' => 'digitizer-two@example.com',
                'first_name' => 'Digitizer',
                'last_name' => 'Two',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 100,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => null,
                'last_name' => null,
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);

        \App\Models\Order::query()->create([
            'order_id' => 200,
            'user_id' => 100,
            'assign_to' => 10,
            'website' => '1dollar',
            'order_type' => 'qquote',
            'status' => 'Underprocess',
            'design_name' => 'Quote Reference Design',
            'format' => 'DST',
            'fabric_type' => 'Twill',
            'sew_out' => 'no',
            'width' => '4',
            'height' => '3',
            'measurement' => 'inch',
            'no_of_colors' => '4',
            'color_names' => 'Red, White, Blue',
            'no_of_appliques' => '2',
            'applique_colors' => 'Gold, Black',
            'end_date' => null,
        ]);

        \App\Models\Attachment::query()->create([
            'id' => 300,
            'order_id' => 200,
            'file_name' => 'quote-reference.png',
            'file_name_with_date' => 'quote-reference-001.png',
            'file_name_with_order_id' => '(200) quote-reference.png',
            'file_source' => 'quote',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->writeUpload('quotes', 'quote-reference-001.png', 'PNGDATA');
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('qucik_quote_users');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        $this->deleteDirectory($this->uploadsRoot);

        parent::tearDown();
    }

    public function test_assigned_team_user_can_download_and_preview_quick_quote_reference_file(): void
    {
        $session = [
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ];

        $this->withSession($session)
            ->get('/team/quick-attachments/300/download')
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->withSession($session)
            ->get('/team/quick-attachments/300/preview/raw')
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_other_team_user_cannot_download_quick_quote_attachment(): void
    {
        $this->withSession([
            'team_user_id' => 11,
            'team_user_name' => 'digitizer-two',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/quick-attachments/300/download')
            ->assertStatus(404);
    }

    public function test_quick_quote_text_download_includes_applique_colors(): void
    {
        $response = $this->withSession([
            'team_user_id' => 10,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ])->get('/team/team_get_design_info_file.php?design_id=200');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('Applique Colors: Gold, Black', $response->streamedContent());
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
