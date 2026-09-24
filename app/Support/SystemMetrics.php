<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Throwable;

class SystemMetrics
{
    /**
     * Get CPU, memory, and storage usage for the dashboard.
     *
     * @return array{
     *     cpu_percent: ?float,
     *     memory_used_bytes: ?int,
     *     memory_total_bytes: ?int,
     *     memory_percent: ?float,
     *     disk_used_bytes: ?int,
     *     disk_total_bytes: ?int,
     *     disk_percent: ?float,
     * }
     */
    public function snapshot(): array
    {
        return Cache::remember('system.metrics', 30, function () {
            if (PHP_OS_FAMILY === 'Windows') {
                $windows = $this->windowsSystemValues();
                $memory = ['used' => $windows['memory_used'], 'total' => $windows['memory_total']];
                $cpu = $windows['cpu'];
            } else {
                $memory = $this->memoryUsage();
                $cpu = $this->cpuPercent();
            }

            $disk = $this->diskUsage();

            return [
                'cpu_percent' => $cpu,
                'memory_used_bytes' => $memory['used'],
                'memory_total_bytes' => $memory['total'],
                'memory_percent' => $this->percent($memory['used'], $memory['total']),
                'disk_used_bytes' => $disk['used'],
                'disk_total_bytes' => $disk['total'],
                'disk_percent' => $this->percent($disk['used'], $disk['total']),
            ];
        });
    }

    public static function formatBytes(?int $bytes): ?string
    {
        if ($bytes === null || $bytes < 0) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        $precision = $unit === 0 || $value >= 100 ? 0 : 1;

        return number_format($value, $precision).' '.$units[$unit];
    }

    public static function formatPercent(?float $percent): ?string
    {
        if ($percent === null) {
            return null;
        }

        return number_format(max(0, min(100, $percent)), 0).'%';
    }

    public static function formatUsage(?int $used, ?int $total): ?string
    {
        $usedDisplay = self::formatBytes($used);
        $totalDisplay = self::formatBytes($total);

        if ($usedDisplay === null || $totalDisplay === null) {
            return null;
        }

        return $usedDisplay.' / '.$totalDisplay;
    }

    public static function barClass(?float $percent): string
    {
        if ($percent === null) {
            return 'bg-gray-200';
        }

        if ($percent >= 90) {
            return 'bg-red-500';
        }

        if ($percent >= 70) {
            return 'bg-amber-500';
        }

        return 'bg-emerald-500';
    }

    private function cpuPercent(): ?float
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => $this->linuxCpuPercent(),
            'Windows' => $this->windowsSystemValues()['cpu'],
            default => null,
        };
    }

    private function linuxCpuPercent(): ?float
    {
        $delta = $this->linuxCpuDelta();

        if ($delta !== null) {
            return round($delta, 1);
        }

        $load = sys_getloadavg()[0] ?? null;
        $cores = $this->linuxCpuCores();

        if ($load === null || ! $cores) {
            return null;
        }

        return round(min(100, ($load / $cores) * 100), 1);
    }

    private function linuxCpuDelta(): ?float
    {
        $current = $this->readProcStat();

        if ($current === null) {
            return null;
        }

        /** @var array{idle: int, total: int}|null $previous */
        $previous = Cache::get('system.cpu_stat');
        Cache::put('system.cpu_stat', $current, now()->addHour());

        if ($previous === null) {
            return null;
        }

        $idleDifference = $current['idle'] - $previous['idle'];
        $totalDifference = $current['total'] - $previous['total'];

        if ($totalDifference <= 0) {
            return null;
        }

        return (1 - ($idleDifference / $totalDifference)) * 100;
    }

    /**
     * @return array{idle: int, total: int}|null
     */
    private function readProcStat(): ?array
    {
        if (! is_readable('/proc/stat')) {
            return null;
        }

        $line = explode("\n", (string) file_get_contents('/proc/stat'))[0] ?? '';
        $values = array_values(array_filter(preg_split('/\s+/', trim((string) $line)) ?: []));
        array_shift($values);
        $values = array_map(intval(...), $values);

        if (count($values) < 4) {
            return null;
        }

        return [
            'idle' => ($values[3] ?? 0) + ($values[4] ?? 0),
            'total' => array_sum($values),
        ];
    }

    private function linuxCpuCores(): int
    {
        $command = $this->runCommand(['nproc']);
        $cores = (int) trim((string) $command);

        return $cores > 0 ? $cores : 1;
    }

    /**
     * @return array{cpu: ?float, memory_used: ?int, memory_total: ?int}
     */
    private function windowsSystemValues(): array
    {
        $powershell = $this->runCommand([
            'powershell',
            '-NoProfile',
            '-NonInteractive',
            '-Command',
            '$cpu = Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average; '.
            '$os = Get-CimInstance Win32_OperatingSystem; '.
            "Write-Output ('CPU=' + \$cpu.Average); ".
            "Write-Output ('Total=' + \$os.TotalVisibleMemorySize); ".
            "Write-Output ('Free=' + \$os.FreePhysicalMemory)",
        ], 5);

        if ($powershell !== null) {
            $values = [];
            foreach (explode("\n", $powershell) as $line) {
                if (preg_match('/^(CPU|Total|Free)=([\d.]+)$/i', trim($line), $matches)) {
                    $values[strtolower($matches[1])] = $matches[2];
                }
            }

            if (isset($values['cpu'], $values['total'], $values['free'])) {
                $total = ((int) $values['total']) * 1024;
                $free = ((int) $values['free']) * 1024;

                return [
                    'cpu' => round((float) $values['cpu'], 1),
                    'memory_used' => max(0, $total - $free),
                    'memory_total' => $total,
                ];
            }
        }

        $cpuOutput = $this->runCommand(['wmic', 'cpu', 'get', 'loadpercentage', '/value']);
        $memory = $this->windowsMemoryUsage();

        $values = [];
        foreach (explode("\n", (string) $cpuOutput) as $line) {
            if (preg_match('/^LoadPercentage=(\d+)/i', trim($line), $matches)) {
                $values[] = (int) $matches[1];
            }
        }

        return [
            'cpu' => $values === [] ? null : round(array_sum($values) / count($values), 1),
            'memory_used' => $memory['used'],
            'memory_total' => $memory['total'],
        ];
    }

    /**
     * @return array{used: ?int, total: ?int}
     */
    private function memoryUsage(): array
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => $this->linuxMemoryUsage(),
            'Windows' => $this->windowsMemoryUsage(),
            default => ['used' => null, 'total' => null],
        };
    }

    /**
     * @return array{used: ?int, total: ?int}
     */
    private function linuxMemoryUsage(): array
    {
        if (! is_readable('/proc/meminfo')) {
            return ['used' => null, 'total' => null];
        }

        $values = [];
        foreach (explode("\n", (string) file_get_contents('/proc/meminfo')) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB$/', trim($line), $matches)) {
                $values[$matches[1]] = ((int) $matches[2]) * 1024;
            }
        }

        $total = $values['MemTotal'] ?? null;
        $available = $values['MemAvailable'] ?? null;

        if ($total === null || $available === null) {
            return ['used' => null, 'total' => null];
        }

        return ['used' => max(0, $total - $available), 'total' => $total];
    }

    /**
     * @return array{used: ?int, total: ?int}
     */
    private function windowsMemoryUsage(): array
    {
        $output = $this->runCommand(['wmic', 'OS', 'get', 'FreePhysicalMemory,TotalVisibleMemorySize', '/value']);

        if ($output === null) {
            return ['used' => null, 'total' => null];
        }

        $values = [];
        foreach (explode("\n", $output) as $line) {
            if (preg_match('/^(FreePhysicalMemory|TotalVisibleMemorySize)=(\d+)$/i', trim($line), $matches)) {
                $values[strtolower($matches[1])] = ((int) $matches[2]) * 1024;
            }
        }

        $total = $values['totalvisiblememorysize'] ?? null;
        $free = $values['freephysicalmemory'] ?? null;

        if ($total === null || $free === null) {
            return ['used' => null, 'total' => null];
        }

        return ['used' => max(0, $total - $free), 'total' => $total];
    }

    /**
     * @return array{used: ?int, total: ?int}
     */
    private function diskUsage(): array
    {
        $path = base_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);

        if ($total === false || $free === false) {
            return ['used' => null, 'total' => null];
        }

        return ['used' => max(0, (int) $total - (int) $free), 'total' => (int) $total];
    }

    private function percent(?int $used, ?int $total): ?float
    {
        if ($used === null || $total === null || $total <= 0) {
            return null;
        }

        return round(($used / $total) * 100, 1);
    }

    private function runCommand(array $command, int $timeout = 2): ?string
    {
        try {
            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            return $process->getOutput();
        } catch (Throwable) {
            return null;
        }
    }
}
