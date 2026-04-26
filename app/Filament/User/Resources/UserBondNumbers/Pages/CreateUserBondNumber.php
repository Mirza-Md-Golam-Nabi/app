<?php

namespace App\Filament\User\Resources\UserBondNumbers\Pages;

use App\Filament\User\Resources\UserBondNumbers\UserBondNumberResource;
use App\Models\UserBondNumber;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUserBondNumber extends CreateRecord
{
    protected static string $resource = UserBondNumberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $existingBonds = UserBondNumber::where('user_id', $data['user_id'])
            ->whereIn('bond_number', collect($data['bond_numbers'])->pluck('bond_number'))
            ->pluck('bond_number')
            ->toArray();

        if (! empty($existingBonds)) {
            throw ValidationException::withMessages([
                'bond_numbers' => 'এই বন্ড নাম্বারগুলি ইতিমধ্যে রয়েছে: '.implode(', ', $existingBonds),
            ]);
        }

        return $data;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::beginTransaction();

            $createdRecords = [];
            $bondNumbers = $data['bond_numbers'];

            foreach ($bondNumbers as $item) {
                $record = static::getModel()::create([
                    'user_id' => $data['user_id'],
                    'bond_holder_id' => $data['bond_holder_id'],
                    'prize_bond_set_id' => $data['prize_bond_set_id'] ?? null,
                    'bond_number' => $item['bond_number'],
                ]);
                $createdRecords[] = $record;
            }

            DB::commit();

            return $createdRecords[0];

        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'bond_numbers' => 'বন্ড নাম্বার সংরক্ষণ করতে ব্যর্থ হয়েছে: '.$e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
