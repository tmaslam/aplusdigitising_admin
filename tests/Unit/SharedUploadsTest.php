<?php

namespace Tests\Unit;

use App\Support\SharedUploads;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SharedUploadsTest extends TestCase
{
    private string $uploadsRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploadsRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'shared-uploads-'.bin2hex(random_bytes(5));
        config()->set('app.shared_uploads_path', $this->uploadsRoot);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->deleteDirectory($this->uploadsRoot);

        parent::tearDown();
    }

    public function test_store_uploaded_file_uses_month_bucket_relative_path(): void
    {
        Carbon::setTestNow('2026-04-25 15:30:00');

        $relativePath = SharedUploads::storeUploadedFile(
            UploadedFile::fake()->create('sample.dst', 10, 'application/octet-stream'),
            'order',
            '2026-04-25_(123)_sample.dst'
        );

        $this->assertSame('2026-04'.DIRECTORY_SEPARATOR.'2026-04-25_(123)_sample.dst', $relativePath);
        $this->assertFileExists($this->uploadsRoot.DIRECTORY_SEPARATOR.'order'.DIRECTORY_SEPARATOR.$relativePath);
    }

    public function test_first_existing_path_supports_month_bucketed_relative_paths(): void
    {
        $path = SharedUploads::path('team'.DIRECTORY_SEPARATOR.'2026-04'.DIRECTORY_SEPARATOR.'proof.dst');
        SharedUploads::ensureParentDirectory($path);
        file_put_contents($path, 'proof');

        $resolved = SharedUploads::firstExistingPath('2026-04/proof.dst', 'team');

        $this->assertSame($path, $resolved);
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $entry) {
            $entryPath = $path.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($entryPath)) {
                $this->deleteDirectory($entryPath);
                continue;
            }

            @unlink($entryPath);
        }

        @rmdir($path);
    }
}
