<?php
namespace App\Services\PrizeBond;

use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PrizeBondOcrService
{
    public function extractFromImage(string $imagePath)
    {
        $fullPath = Storage::disk('public')->path($imagePath);

        if (! file_exists($fullPath)) {
            throw new \Exception("ইমেজ ফাইল পাওয়া যায়নি: {$fullPath}");
        }

        putenv('TESSDATA_PREFIX=' . env('TESSDATA_PREFIX'));

        // Raw text extract
        $text = (new TesseractOCR($fullPath))
            ->executable('C:\\Program Files\\Tesseract-OCR\\tesseract.exe')
            ->lang('ben')
            ->run();

        $text = convertBengaliToEnglish($text);

        return $this->parseText($text);
    }

    private function parseText(string $text)
    {
        $results = [];
        $lines   = array_values(array_filter(
            explode("\n", trim($text)),
            fn($line) => trim($line) !== ''
        ));

        // ৭ সংখ্যার নম্বর extract করার helper
        $extract = fn(string $line): array=>
        preg_match_all('/\b\d{7}\b/', $line, $m) ? $m[0] : [];

        // সব লাইন থেকে সংখ্যা সংগ্রহ
        $allNumbers = [];
        foreach ($lines as $line) {
            foreach ($extract($line) as $number) {
                if (str_starts_with($number, '0000')) {
                    continue;
                }

                $allNumbers[] = $number;
            }
        }

        // ইমেজ structure অনুযায়ী assign:
        // index 0       → 1st prize (১টি)
        // index 1       → 2nd prize (১টি)
        // index 2,3     → 3rd prize (২টি)
        // index 4,5     → 4th prize (২টি)
        // index 6...45  → 5th prize (৪০টি)

        $prizeMap = [
            0 => '1st',
            1 => '2nd',
            2 => '3rd',
            3 => '3rd',
            4 => '4th',
            5 => '4th',
        ];

        foreach ($allNumbers as $index => $number) {
            $results[] = [
                'winning_number' => $number,
                'prize_rank'     => $prizeMap[$index] ?? '5th',
            ];
        }

        return $results;
    }
}
