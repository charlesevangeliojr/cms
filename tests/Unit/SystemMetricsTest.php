<?php

namespace Tests\Unit;

use App\Support\SystemMetrics;
use PHPUnit\Framework\TestCase;

class SystemMetricsTest extends TestCase
{
    public function test_it_formats_bytes_and_usage(): void
    {
        $this->assertSame('0 B', SystemMetrics::formatBytes(0));
        $this->assertSame('1.5 KB', SystemMetrics::formatBytes(1536));
        $this->assertSame('2.0 GB', SystemMetrics::formatBytes(2 * 1024 * 1024 * 1024));
        $this->assertSame('1.5 KB / 3.0 KB', SystemMetrics::formatUsage(1536, 3072));
        $this->assertNull(SystemMetrics::formatUsage(null, 3072));
    }

    public function test_it_formats_percentages_and_status_colours(): void
    {
        $this->assertSame('42%', SystemMetrics::formatPercent(42.4));
        $this->assertNull(SystemMetrics::formatPercent(null));
        $this->assertSame('bg-emerald-500', SystemMetrics::barClass(42));
        $this->assertSame('bg-amber-500', SystemMetrics::barClass(75));
        $this->assertSame('bg-red-500', SystemMetrics::barClass(95));
        $this->assertSame('bg-gray-200', SystemMetrics::barClass(null));
    }
}
