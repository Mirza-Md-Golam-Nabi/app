<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Resources\Schools\Actions\PullStudentReportAction;
use App\Filament\Resources\Schools\Actions\RegenerateSecretAction;
use App\Filament\Resources\Schools\SchoolResource;
use App\Filament\Resources\Schools\Widgets\MonthlyStudentsChart;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    /**
     * The month-by-month bar chart sits right under the Report History table,
     * so the numbers and their shape are read together.
     */
    protected function getFooterWidgets(): array
    {
        return [
            MonthlyStudentsChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            PullStudentReportAction::make(),
            RegenerateSecretAction::make(),
            DeleteAction::make(),
        ];
    }
}
