<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Services\School\SchoolRequestSignature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySchoolSignature
{
    public function __construct(private SchoolRequestSignature $signature) {}

    /**
     * Lets a report through only when a known, active school signed it with
     * its own secret: X-Signature must be the HMAC-SHA256 of
     * "{X-Timestamp}.{raw body}", the timestamp must be recent, and the body
     * must name the same school as the X-School-Id header. The verified
     * school is exposed as the "school" request attribute.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $schoolCode = $request->header('X-School-Id');

        $school = filled($schoolCode)
            ? School::query()->active()->where('school_code', $schoolCode)->first()
            : null;

        $isAuthentic = $school
            && $this->signature->verify(
                $school->secret,
                $request->header('X-Timestamp'),
                $request->getContent(),
                $request->header('X-Signature'),
            )
            && $request->json('school_id') === $school->school_code;

        if (! $isAuthentic) {
            return response()->json(['message' => 'Unauthenticated school.'], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('school', $school);

        return $next($request);
    }
}
