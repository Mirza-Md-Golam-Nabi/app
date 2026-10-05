<?php

namespace App\Http\Controllers;

use App\Enums\SchoolReportSource;
use App\Http\Requests\StoreSchoolReportRequest;
use App\Services\School\StoreSchoolStudentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SchoolReportController extends Controller
{
    /**
     * Receives the student count a school pushes to the central app.
     */
    public function __invoke(StoreSchoolReportRequest $request, StoreSchoolStudentReportService $storeReport): JsonResponse
    {
        $report = $storeReport->handle(
            $request->attributes->get('school'),
            $request->integer('total_students'),
            Carbon::parse($request->validated('reported_at')),
            SchoolReportSource::Push,
        );

        return response()->json(
            ['message' => 'Report received.', 'total_students' => $report->total_students],
            $report->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK,
        );
    }
}
