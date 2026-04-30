<?php

namespace Tests\Unit;

use App\Support\UploadSecurity;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadSecurityTest extends TestCase
{
    public function test_javascript_upload_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('payload.js', 4, 'application/javascript');

        $message = UploadSecurity::assertAllowedFiles([$file], 'source');

        $this->assertSame('Programming files, SQL files, and archive uploads are not allowed.', $message);
    }

    public function test_clean_svg_vector_file_is_allowed(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'svg');
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0H10V10Z"/></svg>');

        $file = new UploadedFile($path, 'vector-art.svg', 'image/svg+xml', null, true);

        $message = UploadSecurity::assertAllowedFiles([$file], 'source');

        $this->assertNull($message);
    }

    public function test_scripted_svg_is_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'svg');
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $file = new UploadedFile($path, 'bad-vector.svg', 'image/svg+xml', null, true);

        $message = UploadSecurity::assertAllowedFiles([$file], 'source');

        $this->assertSame('SVG files with scripts or active content are not allowed.', $message);
    }
}
