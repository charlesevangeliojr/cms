<?php

namespace Tests\Feature\Dashboard;

use App\Models\Role;
use App\Models\User;
use App\Services\GoogleAnalyticsRealtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTrafficTest extends TestCase
{
    use RefreshDatabase;

    private ?string $credentialFixture = null;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google_analytics.property_id' => null, 'services.google_analytics.reporting_timezone' => 'Asia/Manila']);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        if ($this->credentialFixture) {
            unlink($this->credentialFixture);
        }
        parent::tearDown();
    }

    private function configure(): void
    {
        $this->credentialFixture = tempnam(storage_path('framework/testing'), 'ga-test-');
        file_put_contents($this->credentialFixture, json_encode(['client_email' => 'analytics-test@example.com', 'private_key' => 'test-fixture-only']));
        config(['services.google_analytics.property_id' => '123456', 'services.google_analytics.service_account_file' => $this->credentialFixture]);
        Cache::put('google-analytics.access-token.'.sha1('analytics-test@example.com'), 'test-access-token', 3600);
    }

    private function fakeReports(bool $realtimeFailure = false, bool $monthlyFailure = false): void
    {
        Http::fake(function ($request) use ($realtimeFailure, $monthlyFailure) {
            if (str_ends_with($request->url(), ':runReport')) {
                return $monthlyFailure ? Http::response([], 503) : Http::response(['rows' => [['metricValues' => [['value' => '1420']]]]], 200);
            }
            if ($realtimeFailure) {
                return Http::response([], 403);
            }
            if (isset($request['dimensions'])) {
                return Http::response(['rows' => [
                    ['dimensionValues' => [['value' => '0']], 'metricValues' => [['value' => '3']]],
                    ['dimensionValues' => [['value' => '4']], 'metricValues' => [['value' => '3']]],
                    ['dimensionValues' => [['value' => '29']], 'metricValues' => [['value' => '2']]],
                ]]);
            }

            return Http::response(['rows' => [['metricValues' => [['value' => $request['minuteRanges'][0]['startMinutesAgo'] === 4 ? '4' : '7']]]]]);
        });
    }

    public function test_reports_use_distinct_window_totals_and_the_current_calendar_month(): void
    {
        $this->travelTo(Carbon::parse('2026-09-30 16:30:00', 'UTC'));
        $this->configure();
        $this->fakeReports();
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData();
        $this->assertTrue($data['available']);
        $this->assertTrue($data['monthlyAvailable']);
        $this->assertSame(7, $data['active30m']);
        $this->assertSame(4, $data['active5m']);
        $this->assertSame(1420, $data['monthlyUsers']);
        $this->assertSame(30, count($data['minutes']));
        $this->assertSame(2, $data['minutes'][0]);
        $this->assertSame(3, $data['minutes'][25]);
        $this->assertSame(3, $data['minutes'][29]);
        $this->assertSame(0, $data['minutes'][10]);
        $this->assertSame('October 2026', $data['monthLabel']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), ':runReport') && $request['dateRanges'][0] === ['startDate' => '2026-10-01', 'endDate' => 'today'] && $request['metrics'][0]['name'] === 'totalUsers');
        Http::assertSentCount(4);
        app(GoogleAnalyticsRealtime::class)->dashboardData();
        Http::assertSentCount(4);
        $this->travel(61)->seconds();
        app(GoogleAnalyticsRealtime::class)->dashboardData();
        Http::assertSentCount(7);
    }

    public function test_missing_configuration_shows_unavailable_instead_of_zero(): void
    {
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData();
        $this->assertFalse($data['configured']);
        $this->assertNull($data['active30m']);
        $this->assertNull($data['active5m']);
        $this->assertNull($data['monthlyUsers']);
        Http::assertNothingSent();
        $this->actingAs($this->admin())->get(route('dashboard'))->assertOk()->assertSee('Active users in last 30 minutes')->assertSee('Active users in last 5 minutes')->assertSee('Total users this month')->assertDontSee('Connect Google Analytics');
    }

    public function test_monthly_report_failure_does_not_hide_realtime_counts(): void
    {
        $this->configure();
        $this->fakeReports(monthlyFailure: true);
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData();
        $this->assertTrue($data['available']);
        $this->assertSame(7, $data['active30m']);
        $this->assertFalse($data['monthlyAvailable']);
        $this->assertNull($data['monthlyUsers']);
    }

    public function test_realtime_report_failure_does_not_hide_monthly_counts(): void
    {
        $this->configure();
        $this->fakeReports(realtimeFailure: true);
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData();
        $this->assertFalse($data['available']);
        $this->assertNull($data['active30m']);
        $this->assertTrue($data['monthlyAvailable']);
        $this->assertSame(1420, $data['monthlyUsers']);
    }

    public function test_real_zero_counts_render_an_empty_chart(): void
    {
        $this->configure();
        Http::fake(['analyticsdata.googleapis.com/*' => Http::response(['rows' => []])]);
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData();
        $this->assertTrue($data['available']);
        $this->assertSame(0, $data['active30m']);
        $this->assertSame(0, $data['active5m']);
        $this->assertSame(0, $data['monthlyUsers']);
        $response = $this->actingAs($this->admin())->get(route('dashboard'))->assertOk();
        $this->assertSame(30, substr_count($response->getContent(), 'style="height: 0%"'));
    }

    public function test_traffic_chart_bars_include_the_reporting_timezone_and_hover_details(): void
    {
        $this->configure();
        $this->fakeReports();
        $response = $this->actingAs($this->admin())->get(route('dashboard'))->assertOk();
        $response->assertSee('data-traffic-timezone="Asia/Manila"', false)
            ->assertSee('data-traffic-updated-at=', false)
            ->assertSee('data-minute-offset="29"', false)
            ->assertSee('data-traffic-tooltip', false)
            ->assertSee('tabindex="0" role="img"', false);
    }

    public function test_refresh_endpoint_requires_dashboard_permission_and_returns_no_credentials(): void
    {
        $this->get(route('dashboard.traffic'))->assertRedirect(route('login'));
        $denied = User::factory()->create(['role_id' => $this->contentManagerRole()->id, 'is_active' => true, 'permissions' => ['dashboard' => ['view' => false]]]);
        $this->actingAs($denied)->getJson(route('dashboard.traffic'))->assertForbidden();
        $this->configure();
        $this->fakeReports();
        $this->actingAs($this->admin())->getJson(route('dashboard.traffic'))->assertOk()->assertJsonPath('active5m', 4)->assertJsonPath('monthlyUsers', 1420)->assertDontSee('test-access-token')->assertDontSee('private_key');
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::where('name', 'Super Admin')->value('id'), 'is_active' => true, 'permissions' => User::fullAccessPermissions()]);
    }

    public function test_automatic_polling_preserves_the_admin_idle_timeout(): void
    {
        $lastSeen = now()->subMinutes(5)->getTimestamp();
        $this->actingAs($this->admin())->withSession(['last_seen' => $lastSeen])
            ->getJson(route('dashboard.traffic'))->assertOk();
        $this->assertSame($lastSeen, session('last_seen'));
        $this->travel(26)->minutes();
        $this->getJson(route('dashboard.traffic'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_refresh_endpoint_stays_relative_on_local_and_live_hosts(): void
    {
        $this->actingAs($this->admin());
        foreach (['http://localhost:8000', 'https://cms.example.com'] as $origin) {
            $this->get($origin.'/admin/dashboard')->assertOk()
                ->assertSee('data-endpoint="/admin/dashboard/traffic"', false);
        }
    }

    public function test_daily_chart_has_ordered_buckets_and_zeros_for_missing_dates(): void
    {
        $this->travelTo(Carbon::parse('2026-10-08 13:30:00', 'Asia/Manila'));
        $this->configure();
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), ':runReport') && isset($request['dimensions'])) {
                return Http::response(['rows' => [
                    ['dimensionValues' => [['value' => '20261008']], 'metricValues' => [['value' => '5']]],
                    ['dimensionValues' => [['value' => '20261002']], 'metricValues' => [['value' => '2']]],
                    ['dimensionValues' => [['value' => '20260901']], 'metricValues' => [['value' => '99']]],
                ]]);
            }

            return Http::response(['rows' => []]);
        });
        $chart = app(GoogleAnalyticsRealtime::class)->dashboardData('7d')['chart'];
        $this->assertTrue($chart['available']);
        $this->assertSame('7d', $chart['range']);
        $this->assertSame('Active users per day', $chart['title']);
        $this->assertCount(7, $chart['points']);
        $this->assertSame([2, 0, 0, 0, 0, 0, 5], array_column($chart['points'], 'users'));
        $this->assertSame('Oct 2, 2026', $chart['points'][0]['label']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), ':runReport')
            && ($request['dimensions'][0]['name'] ?? null) === 'date'
            && $request['dateRanges'][0] === ['startDate' => '2026-10-02', 'endDate' => '2026-10-08']
            && $request['metrics'][0]['name'] === 'activeUsers');
        Http::assertSentCount(5);
        app(GoogleAnalyticsRealtime::class)->dashboardData('7d');
        Http::assertSentCount(5);
        app(GoogleAnalyticsRealtime::class)->dashboardData('30d');
        Http::assertSentCount(6);
    }

    public function test_today_chart_uses_hours_in_the_reporting_timezone(): void
    {
        $this->travelTo(Carbon::parse('2026-09-30 16:30:00', 'UTC'));
        $this->configure();
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), ':runReport') && isset($request['dimensions'])) {
                return Http::response(['rows' => [
                    ['dimensionValues' => [['value' => '2026100100']], 'metricValues' => [['value' => '4']]],
                ]]);
            }

            return Http::response(['rows' => []]);
        });
        $chart = app(GoogleAnalyticsRealtime::class)->dashboardData('today')['chart'];
        $this->assertCount(1, $chart['points']);
        $this->assertSame(4, $chart['points'][0]['users']);
        $this->assertSame('Oct 1, 12 AM', $chart['points'][0]['label']);
        Http::assertSent(fn ($request) => ($request['dimensions'][0]['name'] ?? null) === 'dateHour'
            && $request['dateRanges'][0] === ['startDate' => '2026-10-01', 'endDate' => '2026-10-01']);
    }

    public function test_day_and_month_ranges_do_not_include_future_dates(): void
    {
        $this->travelTo(Carbon::parse('2026-10-08 13:30:00', 'Asia/Manila'));
        $service = app(GoogleAnalyticsRealtime::class);
        $rolling = $service->dashboardData('30d')['chart'];
        $month = $service->dashboardData('month')['chart'];
        $this->assertCount(30, $rolling['points']);
        $this->assertSame('Sep 9, 2026', $rolling['points'][0]['label']);
        $this->assertSame('Oct 8, 2026', $rolling['points'][29]['label']);
        $this->assertCount(8, $month['points']);
        $this->assertSame('Oct 1, 2026', $month['points'][0]['label']);
        $this->assertFalse($rolling['available']);
        Http::assertNothingSent();
    }

    public function test_historical_chart_failure_does_not_hide_summary_counts(): void
    {
        $this->configure();
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), ':runReport') && isset($request['dimensions'])) {
                return Http::response([], 503);
            }

            return Http::response(['rows' => [['metricValues' => [['value' => '3']]]]]);
        });
        $data = app(GoogleAnalyticsRealtime::class)->dashboardData('7d');
        $this->assertTrue($data['available']);
        $this->assertTrue($data['monthlyAvailable']);
        $this->assertSame(3, $data['active30m']);
        $this->assertFalse($data['chart']['available']);
    }

    public function test_dropdown_and_refresh_endpoint_support_only_allowed_ranges(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard'))->assertOk()
            ->assertSee('data-traffic-range', false)->assertSee('Last 7 days')->assertSee('This month');
        foreach (['30m', 'today', '7d', '30d', 'month'] as $range) {
            $this->getJson(route('dashboard.traffic', ['range' => $range]))
                ->assertOk()->assertJsonPath('chart.range', $range)->assertJsonPath('chart.available', false);
        }
        $this->getJson(route('dashboard.traffic', ['range' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('range');
        $this->getJson(route('dashboard.traffic', ['range' => ['7d']]))->assertUnprocessable();
        Http::assertNothingSent();
    }
}
