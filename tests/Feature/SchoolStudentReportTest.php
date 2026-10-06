<?php

use App\Enums\SchoolReportSource;
use App\Enums\UserType;
use App\Filament\Resources\Schools\Pages\CreateSchool;
use App\Filament\Resources\Schools\Pages\EditSchool;
use App\Filament\Resources\Schools\Pages\ListSchools;
use App\Filament\Resources\Schools\SchoolResource;
use App\Filament\Resources\Schools\Widgets\MonthlyStudentsChart;
use App\Filament\Widgets\SchoolStatsOverview;
use App\Models\School;
use App\Models\SchoolStudentReport;
use App\Models\User;
use App\Services\School\PullSchoolStudentReportService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeReportingSchool(array $attributes = []): School
{
    return School::factory()->create([
        'name' => 'Al Helal Academy',
        'school_code' => 'school-42',
        'secret' => 'test-secret',
        'base_url' => 'https://school.test',
        ...$attributes,
    ]);
}

/**
 * Send a report exactly the way a school installation does: the raw JSON
 * body signed together with the timestamp.
 *
 * @param  array<string, mixed>  $overrides  headers to replace, e.g. a wrong signature
 */
function pushSchoolReport(array $body, string $secret = 'test-secret', ?int $timestamp = null, array $overrides = [])
{
    $rawBody = json_encode($body);
    $timestamp = (string) ($timestamp ?? now()->timestamp);

    $headers = [
        'X-School-Id' => $body['school_id'] ?? '',
        'X-Timestamp' => $timestamp,
        'X-Signature' => hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret),
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        ...$overrides,
    ];

    $server = collect($headers)
        ->mapWithKeys(fn (string $value, string $name): array => [
            ($name === 'Content-Type' ? 'CONTENT_TYPE' : 'HTTP_'.str_replace('-', '_', strtoupper($name))) => $value,
        ])
        ->all();

    return test()->call('POST', '/api/school-reports', [], [], [], $server, $rawBody);
}

function schoolReportBody(array $overrides = []): array
{
    return [
        'school_id' => 'school-42',
        'total_students' => 480,
        'reported_at' => '2026-10-15T00:00:00+06:00',
        ...$overrides,
    ];
}

/**
 * Fill in the year and month columns of a report row built by hand in a test.
 *
 * @param  array<string, mixed>  $attributes
 * @return array<string, mixed>
 */
function reportMonthFor(array $attributes): array
{
    return [
        ...$attributes,
        'report_year' => $attributes['reported_at']->year,
        'report_month' => $attributes['reported_at']->month,
    ];
}

function actingAsSchoolAdmin(): User
{
    $admin = User::factory()->create(['user_type' => UserType::Admin]);

    test()->actingAs($admin);

    return $admin;
}

it('gives a new school its own id and secret', function () {
    $first = School::factory()->create();
    $second = School::factory()->create();

    expect($first->school_code)->toStartWith('SCH-')
        ->and($first->school_code)->not->toBe($second->school_code)
        ->and(strlen($first->secret))->toBe(48)
        ->and($first->secret)->not->toBe($second->secret)
        ->and($first->getRawOriginal('secret'))->not->toBe($first->secret)
        ->and($first->envValues())->toBe([
            'CENTRAL_URL' => rtrim(config('app.url'), '/'),
            'CENTRAL_SCHOOL_ID' => $first->school_code,
            'CENTRAL_SECRET' => $first->secret,
        ]);
});

it('stores a correctly signed report pushed by a school', function () {
    $school = makeReportingSchool();

    pushSchoolReport(schoolReportBody())
        ->assertCreated()
        ->assertJsonPath('total_students', 480);

    $report = SchoolStudentReport::sole();

    expect($report->school_id)->toBe($school->id)
        ->and($report->total_students)->toBe(480)
        ->and($report->source)->toBe(SchoolReportSource::Push)
        ->and($report->reported_at->equalTo('2026-10-15T00:00:00+06:00'))->toBeTrue();
});

it('keeps one record per school per month, updating it when another report arrives that month', function () {
    $school = makeReportingSchool();

    pushSchoolReport(schoolReportBody(['total_students' => 480, 'reported_at' => '2026-10-15T00:00:00+06:00']))->assertCreated();
    pushSchoolReport(schoolReportBody(['total_students' => 480, 'reported_at' => '2026-10-15T00:00:00+06:00']))->assertOk();
    pushSchoolReport(schoolReportBody(['total_students' => 492, 'reported_at' => '2026-10-28T10:30:00+06:00']))->assertOk();

    $october = SchoolStudentReport::sole();

    expect($october->total_students)->toBe(492)
        ->and($october->report_year)->toBe(2026)
        ->and($october->report_month)->toBe(10)
        ->and($october->period_label)->toBe('October 2026')
        ->and($october->reported_at->equalTo('2026-10-28T10:30:00+06:00'))->toBeTrue();

    pushSchoolReport(schoolReportBody(['total_students' => 495, 'reported_at' => '2026-11-15T00:00:00+06:00']))->assertCreated();

    expect($school->reports()->count())->toBe(2)
        ->and($school->fresh()->latestReport->total_students)->toBe(495);
});

it('stores a new record for the same month of a different year', function () {
    $school = makeReportingSchool();

    pushSchoolReport(schoolReportBody(['total_students' => 480, 'reported_at' => '2026-10-15T00:00:00+06:00']))->assertCreated();
    pushSchoolReport(schoolReportBody(['total_students' => 530, 'reported_at' => '2027-10-15T00:00:00+06:00']))->assertCreated();

    expect($school->reports()->orderBy('report_year')->pluck('total_students', 'report_year')->all())
        ->toBe([2026 => 480, 2027 => 530]);
});

it('replaces the month\'s pushed record when the same month is fetched, and keeps schools apart', function () {
    $school = makeReportingSchool();
    $other = School::factory()->create(['school_code' => 'school-77', 'secret' => 'other-secret']);

    $this->travelTo('2026-10-20 09:00:00');

    pushSchoolReport(schoolReportBody(['total_students' => 480, 'reported_at' => now()->subDays(5)->toIso8601String()]))->assertCreated();
    pushSchoolReport(schoolReportBody(['school_id' => 'school-77', 'total_students' => 90, 'reported_at' => now()->toIso8601String()]), secret: 'other-secret')->assertCreated();

    Http::fake(['school.test/*' => Http::response([
        'school_id' => 'school-42',
        'total_students' => 501,
        'reported_at' => now()->toIso8601String(),
    ])]);

    app(PullSchoolStudentReportService::class)->handle($school);

    $report = $school->reports()->sole();

    expect($report->total_students)->toBe(501)
        ->and($report->source)->toBe(SchoolReportSource::Pull)
        ->and($other->reports()->sole()->total_students)->toBe(90);
});

it('rejects a report that is not authentically from the school it names', function (Closure $send) {
    makeReportingSchool();

    $send()->assertUnauthorized();

    expect(SchoolStudentReport::count())->toBe(0);
})->with([
    'wrong secret' => [fn () => pushSchoolReport(schoolReportBody(), secret: 'someone-else')],
    'unknown school' => [fn () => pushSchoolReport(schoolReportBody(['school_id' => 'school-99']))],
    'ten minutes old' => [fn () => pushSchoolReport(schoolReportBody(), timestamp: now()->subMinutes(10)->timestamp)],
    'header names another school than the body' => [fn () => pushSchoolReport(schoolReportBody(['school_id' => 'school-99']), overrides: ['X-School-Id' => 'school-42'])],
    'tampered signature' => [fn () => pushSchoolReport(schoolReportBody(), overrides: ['X-Signature' => str_repeat('a', 64)])],
    'no signature headers' => [fn () => test()->postJson('/api/school-reports', schoolReportBody())],
]);

it('rejects a report from a school that has been deactivated', function () {
    makeReportingSchool(['is_active' => false]);

    pushSchoolReport(schoolReportBody())->assertUnauthorized();

    expect(SchoolStudentReport::count())->toBe(0);
});

it('rejects an authentic report whose numbers are unusable', function (array $overrides, string $field) {
    makeReportingSchool();

    pushSchoolReport(schoolReportBody($overrides))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    expect(SchoolStudentReport::count())->toBe(0);
})->with([
    'negative count' => [['total_students' => -1], 'total_students'],
    'count is not a number' => [['total_students' => 'many'], 'total_students'],
    'bad date' => [['reported_at' => 'yesterday-ish'], 'reported_at'],
]);

it('pulls the student count from a school with a signed request and stores it', function () {
    $school = makeReportingSchool();

    Http::fake(['school.test/*' => Http::response([
        'school_id' => 'school-42',
        'total_students' => 512,
        'reported_at' => '2026-10-20T09:30:00+06:00',
    ])]);

    $this->artisan('schools:pull-student-reports')
        ->expectsOutputToContain('Al Helal Academy: 512 student(s)')
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        $timestamp = $request->header('X-Timestamp')[0];

        return $request->url() === 'https://school.test/api/central/student-report'
            && $request->method() === 'GET'
            && $request->header('X-Signature')[0] === hash_hmac('sha256', "{$timestamp}.school-42", 'test-secret');
    });

    $report = $school->reports()->sole();

    expect($report->total_students)->toBe(512)
        ->and($report->source)->toBe(SchoolReportSource::Pull)
        ->and($school->fresh()->latestReport->is($report))->toBeTrue();
});

it('stores nothing when a pull fails, and says why', function (Closure $fake, string $expected) {
    makeReportingSchool();
    $fake();

    $this->artisan('schools:pull-student-reports')
        ->expectsOutputToContain($expected)
        ->assertFailed();

    expect(SchoolStudentReport::count())->toBe(0);
})->with([
    'school refuses the signature' => [fn () => Http::fake(['school.test/*' => Http::response(['message' => 'Unauthenticated central server.'], 401)]), '401'],
    'school server is down' => [fn () => Http::fake(fn () => throw new ConnectionException('timed out')), 'সংযোগ করা যায়নি'],
    'school server errors' => [fn () => Http::fake(['school.test/*' => Http::response('oops', 500)]), '500'],
    'answer is for another school' => [fn () => Http::fake(['school.test/*' => Http::response(['school_id' => 'school-99', 'total_students' => 5, 'reported_at' => now()->toIso8601String()])]), 'প্রত্যাশিত রিপোর্টের মতো নয়'],
    'answer is not a report' => [fn () => Http::fake(['school.test/*' => Http::response(['hello' => 'world'])]), 'প্রত্যাশিত রিপোর্টের মতো নয়'],
]);

it('skips inactive schools and schools without a url when pulling everything', function () {
    Http::fake();

    makeReportingSchool(['is_active' => false]);
    School::factory()->create(['base_url' => null]);

    $this->artisan('schools:pull-student-reports')->assertSuccessful();

    Http::assertNothingSent();
});

it('flags a school as overdue when it has not reported within the expected window', function () {
    $school = makeReportingSchool();

    expect($school->isStale())->toBeTrue();

    $school->reports()->create(reportMonthFor(['total_students' => 10, 'reported_at' => now()->subDays(36), 'source' => SchoolReportSource::Push]));
    expect($school->fresh()->isStale())->toBeTrue();

    $school->reports()->create(reportMonthFor(['total_students' => 12, 'reported_at' => now()->subDays(3), 'source' => SchoolReportSource::Push]));
    expect($school->fresh()->isStale())->toBeFalse();
});

it('lists schools with their latest student count for an admin', function () {
    actingAsSchoolAdmin();

    $school = makeReportingSchool();
    $school->reports()->create(reportMonthFor(['total_students' => 300, 'reported_at' => now()->subMonths(2), 'source' => SchoolReportSource::Push]));
    $school->reports()->create(reportMonthFor(['total_students' => 345, 'reported_at' => now()->subDay(), 'source' => SchoolReportSource::Push]));
    $silent = School::factory()->create(['name' => 'Silent School']);

    Livewire::test(ListSchools::class)
        ->assertCanSeeTableRecords([$school, $silent])
        ->assertTableColumnStateSet('latestReport.total_students', 345, $school)
        ->assertTableColumnStateSet('report_status', 'Up to date', $school)
        ->assertTableColumnStateSet('report_status', 'No report yet', $silent);
});

it('keeps the schools page away from ordinary users', function () {
    $this->actingAs(User::factory()->create(['user_type' => UserType::User]));

    $this->get('/admin/schools')->assertForbidden();
});

it('fetches one school from its table row', function () {
    actingAsSchoolAdmin();
    $school = makeReportingSchool();

    Http::fake(['school.test/*' => Http::response([
        'school_id' => 'school-42',
        'total_students' => 77,
        'reported_at' => now()->toIso8601String(),
    ])]);

    Livewire::test(ListSchools::class)
        ->callAction(TestAction::make('pullStudentReport')->table($school))
        ->assertNotified('Al Helal Academy: 77 জন student');

    expect($school->reports()->sole()->total_students)->toBe(77);
});

it('reports which schools could not be reached when fetching all', function () {
    actingAsSchoolAdmin();
    makeReportingSchool();
    $down = School::factory()->create(['name' => 'Down School', 'base_url' => 'https://down.test']);

    Http::fake([
        'school.test/*' => Http::response(['school_id' => 'school-42', 'total_students' => 60, 'reported_at' => now()->toIso8601String()]),
        'down.test/*' => Http::response('oops', 500),
    ]);

    Livewire::test(ListSchools::class)
        ->callAction('pullAllStudentReports')
        ->assertNotified();

    expect(SchoolStudentReport::count())->toBe(1)
        ->and($down->reports()->count())->toBe(0);
});

it('creates a school from the admin panel and shows its env values on its page', function () {
    actingAsSchoolAdmin();

    Livewire::test(CreateSchool::class)
        ->fillForm(['name' => 'New School', 'base_url' => 'https://new-school.test'])
        ->call('create')
        ->assertHasNoFormErrors();

    $school = School::where('name', 'New School')->sole();

    expect($school->school_code)->toStartWith('SCH-')
        ->and($school->is_active)->toBeTrue();

    Livewire::test(EditSchool::class, ['record' => $school->id])
        ->assertSee($school->school_code)
        ->assertSee($school->secret)
        ->assertSee('CENTRAL_SECRET');
});

it('sums the latest count of every active school on the overview', function () {
    actingAsSchoolAdmin();

    $first = makeReportingSchool();
    $first->reports()->create(reportMonthFor(['total_students' => 900, 'reported_at' => now()->subMonths(2), 'source' => SchoolReportSource::Push]));
    $first->reports()->create(reportMonthFor(['total_students' => 1000, 'reported_at' => now()->subDay(), 'source' => SchoolReportSource::Push]));

    $second = School::factory()->create();
    $second->reports()->create(reportMonthFor(['total_students' => 234, 'reported_at' => now()->subDays(2), 'source' => SchoolReportSource::Pull]));

    $inactive = School::factory()->inactive()->create();
    $inactive->reports()->create(reportMonthFor(['total_students' => 5000, 'reported_at' => now(), 'source' => SchoolReportSource::Push]));

    School::factory()->create(['name' => 'Never Reported']);

    Livewire::test(SchoolStatsOverview::class)
        ->assertSee('1,234')
        ->assertDontSee('6,234');
});

it('replaces the secret from the school page and stops accepting the old one', function () {
    actingAsSchoolAdmin();
    $school = makeReportingSchool();

    Livewire::test(EditSchool::class, ['record' => $school->id])
        ->assertSee('test-secret')
        ->callAction('regenerateSecret')
        ->assertNotified('নতুন secret তৈরি হয়েছে')
        ->assertDontSee('test-secret')
        ->assertSee($school->fresh()->secret);

    $newSecret = $school->fresh()->secret;

    expect($newSecret)->not->toBe('test-secret')
        ->and(strlen($newSecret))->toBe(48)
        ->and($school->fresh()->school_code)->toBe('school-42');

    pushSchoolReport(schoolReportBody(), secret: 'test-secret')->assertUnauthorized();
    pushSchoolReport(schoolReportBody(), secret: $newSecret)->assertCreated();
});

it('signs pull requests with the new secret after it is regenerated', function () {
    $school = makeReportingSchool();
    $school->regenerateSecret();

    Http::fake(['school.test/*' => Http::response([
        'school_id' => 'school-42',
        'total_students' => 9,
        'reported_at' => now()->toIso8601String(),
    ])]);

    $this->artisan('schools:pull-student-reports')->assertSuccessful();

    Http::assertSent(function (Request $request) use ($school): bool {
        $timestamp = $request->header('X-Timestamp')[0];

        return $request->header('X-Signature')[0] === hash_hmac('sha256', "{$timestamp}.school-42", $school->fresh()->secret)
            && $request->header('X-Signature')[0] !== hash_hmac('sha256', "{$timestamp}.school-42", 'test-secret');
    });
});

it('links the overdue stat to the schools list showing only the overdue schools', function () {
    actingAsSchoolAdmin();

    $upToDate = makeReportingSchool();
    $upToDate->reports()->create(reportMonthFor(['total_students' => 50, 'reported_at' => now()->subDays(3), 'source' => SchoolReportSource::Push]));

    $lapsed = School::factory()->create(['name' => 'Lapsed School']);
    $lapsed->reports()->create(reportMonthFor(['total_students' => 40, 'reported_at' => now()->subDays(60), 'source' => SchoolReportSource::Push]));

    $neverReported = School::factory()->create(['name' => 'Never Reported']);
    $inactive = School::factory()->inactive()->create(['name' => 'Closed School']);

    $overdueUrl = SchoolResource::getUrl('index', ['filters' => ['overdue' => ['isActive' => true]]]);

    Livewire::test(SchoolStatsOverview::class)->assertSeeHtml(e($overdueUrl));

    expect(School::overdue()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$lapsed->id, $neverReported->id])->sort()->values()->all());

    Livewire::test(ListSchools::class)
        ->assertCanSeeTableRecords([$upToDate, $lapsed, $neverReported, $inactive])
        ->filterTable('overdue')
        ->assertCanNotSeeTableRecords([$upToDate, $inactive]);

    // Opened through the stat's link, the list arrives already filtered.
    Livewire::withQueryParams(['filters' => ['overdue' => ['isActive' => true]]])
        ->test(ListSchools::class)
        ->assertCanSeeTableRecords([$lapsed, $neverReported])
        ->assertCanNotSeeTableRecords([$upToDate, $inactive]);
});

it('charts all twelve months of the chosen year, leaving months without a report empty', function () {
    actingAsSchoolAdmin();
    $this->travelTo('2027-03-10 10:00:00');

    $school = makeReportingSchool();
    foreach ([['2026-10-15', 480], ['2026-11-15', 492], ['2027-01-15', 510], ['2027-02-15', 515]] as [$date, $count]) {
        $school->reports()->create(reportMonthFor(['total_students' => $count, 'reported_at' => Carbon\Carbon::parse($date), 'source' => SchoolReportSource::Push]));
    }
    School::factory()->create()->reports()->create(reportMonthFor(['total_students' => 999, 'reported_at' => Carbon\Carbon::parse('2027-01-20'), 'source' => SchoolReportSource::Push]));

    $chart = Livewire::test(MonthlyStudentsChart::class, ['record' => $school]);

    expect($chart->get('filter'))->toBe('2027');

    $data = (fn () => $this->getData())->call($chart->instance());

    expect($data['labels'])->toHaveCount(12)
        ->and($data['labels'][0])->toBe('Jan')
        ->and($data['datasets'][0]['data'])->toBe([510, 515, null, null, null, null, null, null, null, null, null, null]);

    $chart->set('filter', '2026');
    $data = (fn () => $this->getData())->call($chart->instance());

    expect($data['datasets'][0]['data'])->toBe([null, null, null, null, null, null, null, null, null, 480, 492, null])
        ->and((fn () => $this->getFilters())->call($chart->instance()))->toBe(['2027' => '2027', '2026' => '2026']);
});

it('shows the monthly chart on the school page', function () {
    actingAsSchoolAdmin();
    $school = makeReportingSchool();

    Livewire::test(EditSchool::class, ['record' => $school->id])
        ->assertSeeLivewire(MonthlyStudentsChart::class);
});
