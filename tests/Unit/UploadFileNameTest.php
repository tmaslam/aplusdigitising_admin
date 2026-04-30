<?php

namespace Tests\Unit;

use App\Support\UploadFileName;
use Tests\TestCase;

class UploadFileNameTest extends TestCase
{
    public function test_sanitize_preserves_customer_visible_punctuation(): void
    {
        $clean = UploadFileName::sanitize("Ioanna's Hair Salon, Logo #1.PDF");

        $this->assertSame("Ioanna's Hair Salon, Logo #1.PDF", $clean);
    }

    public function test_sanitize_removes_path_and_reserved_characters(): void
    {
        $clean = UploadFileName::sanitize('..\\bad/name:*?"<>|.pdf');

        $this->assertSame('..badname.pdf', $clean);
    }
}
