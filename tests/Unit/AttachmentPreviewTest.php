<?php

namespace Tests\Unit;

use App\Support\AttachmentPreview;
use PHPUnit\Framework\TestCase;

class AttachmentPreviewTest extends TestCase
{
    public function test_it_only_allows_expected_preview_extensions(): void
    {
        $this->assertTrue(AttachmentPreview::isSupported('proof.pdf'));
        $this->assertTrue(AttachmentPreview::isSupported('preview.jpeg'));
        $this->assertTrue(AttachmentPreview::isSupported('notes.json'));
        $this->assertFalse(AttachmentPreview::isSupported('design.dst'));
        $this->assertFalse(AttachmentPreview::isSupported('vector.svg'));
    }

    public function test_it_maps_content_types_consistently(): void
    {
        $this->assertSame('application/pdf', AttachmentPreview::contentType('proof.pdf'));
        $this->assertSame('image/png', AttachmentPreview::contentType('preview.png'));
        $this->assertSame('text/plain; charset=utf-8', AttachmentPreview::contentType('notes.log'));
        $this->assertSame('application/octet-stream', AttachmentPreview::contentType('archive.bin'));
    }
}
