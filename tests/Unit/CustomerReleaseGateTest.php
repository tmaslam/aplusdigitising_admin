<?php

namespace Tests\Unit;

use App\Models\Attachment;
use App\Support\CustomerReleaseGate;
use PHPUnit\Framework\TestCase;

class CustomerReleaseGateTest extends TestCase
{
    public function test_preview_safe_extensions_are_classified_correctly(): void
    {
        $pdf = new Attachment(['file_name' => 'proof-sheet.pdf']);
        $jpg = new Attachment(['file_name' => 'preview-image.jpg']);
        $dst = new Attachment(['file_name' => 'production-file.dst']);
        $datedPdf = new Attachment([
            'file_name' => '',
            'file_name_with_order_id' => '',
            'file_name_with_date' => 'proof-sheet-20260330.pdf',
        ]);

        $this->assertTrue(CustomerReleaseGate::isPreviewAttachment($pdf));
        $this->assertTrue(CustomerReleaseGate::isPreviewAttachment($jpg));
        $this->assertTrue(CustomerReleaseGate::isPreviewAttachment($datedPdf));
        $this->assertFalse(CustomerReleaseGate::isPreviewAttachment($dst));
    }
}
