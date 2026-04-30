<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Support\OrderWorkflow;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    public function test_quote_management_records_use_quote_flow_label(): void
    {
        $order = new Order([
            'order_type' => 'digitzing',
            'type' => 'digitizing',
        ]);

        $this->assertSame('Quote', OrderWorkflow::flowContextLabel($order));
        $this->assertSame('Digitizing', OrderWorkflow::workTypeLabel($order));
    }

    public function test_order_management_records_keep_order_flow_label(): void
    {
        $order = new Order([
            'order_type' => 'order',
            'type' => 'digitizing',
        ]);

        $this->assertSame('Order', OrderWorkflow::flowContextLabel($order));
    }
}
