<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleAnalyticsRealtime
{
    public function dashboardData(): array
    {
        $propertyId = config('services.google_analytics.property_id');
        $credentialFile = $this->credentialPath();
        $configured = filled($propertyId) && $credentialFile && is_file($credentialFile);
        $timezone = config('services.google_analytics.reporting_timezone', 'Asia/Manila');
        $month = now($timezone)->startOfMonth();
        $data = [
            'configured' => (bool) $configured, 'available' => false, 'monthlyAvailable' => false,
            'active30m' => null, 'active5m' => null, 'activeNow' => null,
            'monthlyUsers' => null, 'minutes' => array_fill(0, 30, 0),
            'monthLabel' => $month->format('F Y'), 'monthStart' => $month->toDateString(),
            'timezone' => $timezone, 'realtimeUpdatedAt' => null, 'monthlyUpdatedAt' => null,
        ];

        if (! $configured) {
            return $data;
        }

        // Cache the two reports independently: a historical-report failure must
        // not hide successful realtime data, or vice versa.
        $key = sha1($propertyId.'|'.$credentialFile);
        $realtime = Cache::remember('google-analytics.realtime.v2.'.$key, 60, function () use ($propertyId, $credentialFile) {
            try {
                return $this->realtimeReport((string) $propertyId, $this->accessToken($credentialFile));
            } catch (Throwable $exception) {
                $this->logReportFailure('Realtime', $exception);

                return ['available' => false];
            }
        });
        $monthly = Cache::remember('google-analytics.monthly.'.$key.'.'.$timezone.'.'.$month->toDateString(), 900, function () use ($propertyId, $credentialFile, $month) {
            try {
                $endpoint = 'https://analyticsdata.googleapis.com/v1beta/properties/'.rawurlencode((string) $propertyId).':runReport';
                $response = Http::withToken($this->accessToken($credentialFile))->timeout(8)->post($endpoint, [
                    'dateRanges' => [['startDate' => $month->toDateString(), 'endDate' => 'today']],
                    'metrics' => [['name' => 'totalUsers']],
                ]);
                $response->throw();

                return ['monthlyAvailable' => true, 'monthlyUsers' => (int) ($response->json('rows.0.metricValues.0.value') ?? 0), 'monthlyUpdatedAt' => now()->toIso8601String()];
            } catch (Throwable $exception) {
                $this->logReportFailure('monthly', $exception);

                return ['monthlyAvailable' => false];
            }
        });

        return array_merge($data, $realtime, $monthly);
    }

    private function realtimeReport(string $propertyId, string $token): array
    {
        $endpoint = 'https://analyticsdata.googleapis.com/v1beta/properties/'.rawurlencode($propertyId).':runRealtimeReport';
        $summary = [];
        foreach (['active30m' => 29, 'active5m' => 4] as $name => $startMinutesAgo) {
            // Deduplicate users over the complete window; summing minute rows
            // would count the same person multiple times.
            $response = Http::withToken($token)->timeout(8)->post($endpoint, [
                'metrics' => [['name' => 'activeUsers']],
                'minuteRanges' => [['startMinutesAgo' => $startMinutesAgo, 'endMinutesAgo' => 0]],
            ]);
            $response->throw();
            $summary[$name] = (int) ($response->json('rows.0.metricValues.0.value') ?? 0);
        }
        $response = Http::withToken($token)->timeout(8)->post($endpoint, [
            'dimensions' => [['name' => 'minutesAgo']],
            'metrics' => [['name' => 'activeUsers']],
            'minuteRanges' => [['startMinutesAgo' => 29, 'endMinutesAgo' => 0]],
            'limit' => '30',
        ]);
        $response->throw();
        $minutes = array_fill(0, 30, 0);
        foreach ($response->json('rows', []) as $row) {
            $minute = filter_var($row['dimensionValues'][0]['value'] ?? null, FILTER_VALIDATE_INT);
            if ($minute !== false && $minute >= 0 && $minute < 30) {
                $minutes[29 - $minute] = (int) ($row['metricValues'][0]['value'] ?? 0);
            }
        }

        return [...$summary, 'available' => true, 'activeNow' => $minutes[29], 'minutes' => $minutes, 'realtimeUpdatedAt' => now()->toIso8601String()];
    }

    private function credentialPath(): ?string
    {
        $configuredPath = config('services.google_analytics.service_account_file');
        if (! is_string($configuredPath) || trim($configuredPath) === '') {
            return null;
        }

        return str_starts_with($configuredPath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $configuredPath)
            ? $configuredPath
            : storage_path($configuredPath);
    }

    private function accessToken(string $credentialFile): string
    {
        $credentials = json_decode((string) file_get_contents($credentialFile), true, flags: JSON_THROW_ON_ERROR);
        $email = $credentials['client_email'] ?? null;
        $privateKey = $credentials['private_key'] ?? null;
        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        if (! is_string($email) || ! is_string($privateKey)) {
            throw new \RuntimeException('The Google service account file is missing client_email or private_key.');
        }

        $cacheKey = 'google-analytics.access-token.'.sha1($email);

        return Cache::remember($cacheKey, 3300, function () use ($email, $privateKey, $tokenUri) {
            $now = time();
            $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $this->base64Url(json_encode([
                'iss' => $email,
                'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsignedToken = $header.'.'.$claims;
            $key = openssl_pkey_get_private($privateKey);
            if (! $key || ! openssl_sign($unsignedToken, $signature, $key, OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Could not sign the Google Analytics service account token.');
            }

            $response = Http::asForm()->timeout(8)->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsignedToken.'.'.$this->base64Url($signature),
            ]);
            $response->throw();
            $accessToken = $response->json('access_token');
            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Google did not return an access token.');
            }

            return $accessToken;
        });
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function logReportFailure(string $report, Throwable $exception): void
    {
        $context = ['exception' => $exception::class];
        if ($exception instanceof \Illuminate\Http\Client\RequestException) {
            $context['http_status'] = $exception->response->status();
            $context['google_message'] = $exception->response->json('error.message');
        } else {
            $context['reason'] = $exception->getMessage();
        }
        Log::warning("Google Analytics {$report} report could not be loaded.", $context);
    }
}
