<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolReportRequest extends FormRequest
{
    /**
     * The school is authenticated by its signature (VerifySchoolSignature) before this runs.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'string'],
            'total_students' => ['required', 'integer', 'min:0'],
            'reported_at' => ['required', 'date'],
        ];
    }
}
