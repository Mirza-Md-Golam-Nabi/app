<?php

use App\Http\Controllers\SchoolReportController;
use App\Http\Middleware\VerifySchoolSignature;
use Illuminate\Support\Facades\Route;

// প্রতিটা স্কুলের সফটওয়্যার তার মোট student সংখ্যা এখানে পাঠায় (মাসে একবার, বা হাতে চালালে)
Route::post('/school-reports', SchoolReportController::class)
    ->middleware(['throttle:60,1', VerifySchoolSignature::class])
    ->name('api.school-reports.store');
