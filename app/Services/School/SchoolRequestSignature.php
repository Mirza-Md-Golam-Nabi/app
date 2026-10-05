<?php

namespace App\Services\School;

/**
 * Signs and verifies the requests exchanged with a school installation. Both
 * sides hold the school's secret and sign "{timestamp}.{payload}" with
 * HMAC-SHA256 — the payload is the raw JSON body for a report the school
 * pushes, and the school's code for a pull request the central app makes.
 */
class SchoolRequestSignature
{
    public function sign(string $secret, string $timestamp, string $payload): string
    {
        return hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
    }

    /**
     * True when the signature matches and the timestamp is recent enough that
     * the request cannot be a replay of an old captured one.
     */
    public function verify(string $secret, ?string $timestamp, string $payload, ?string $signature): bool
    {
        if (blank($timestamp) || blank($signature) || ! ctype_digit($timestamp)) {
            return false;
        }

        if (abs(now()->timestamp - (int) $timestamp) > config('schools.signature_tolerance_seconds')) {
            return false;
        }

        return hash_equals($this->sign($secret, $timestamp, $payload), $signature);
    }
}
