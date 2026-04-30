<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Support\TurnaroundTracking;
use Carbon\Carbon;
use Tests\TestCase;

class TurnaroundTrackingTest extends TestCase
{
    public function test_overdue_label_keeps_detailed_time_within_seventy_two_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-12 12:00:00'));

        $order = new Order();
        $order->turn_around_time = 'standard';
        $order->submit_date = '2026-04-11 00:00:00';
        $order->completion_date = '2026-04-10 13:30:00';
        $order->status = 'in process';

        $summary = TurnaroundTracking::summary($order);

        $this->assertSame('Past Due', $summary['status_label']);
        $this->assertSame('46h 30m overdue', $summary['remaining_label']);

        Carbon::setTestNow();
    }

    public function test_overdue_label_switches_to_generic_message_after_seventy_two_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-12 12:00:00'));

        $order = new Order();
        $order->turn_around_time = 'standard';
        $order->submit_date = '2026-04-01 00:00:00';
        $order->completion_date = '2026-04-05 03:00:00';
        $order->status = 'in process';

        $summary = TurnaroundTracking::summary($order);

        $this->assertSame('Past Due', $summary['status_label']);
        $this->assertSame('Overdue - needs attention', $summary['remaining_label']);

        Carbon::setTestNow();
    }
}
