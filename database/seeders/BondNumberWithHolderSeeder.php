<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBondNumber;
use Illuminate\Database\Seeder;

class BondNumberWithHolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'user1@example.com')->first();
        foreach ($this->bondHolders() as $name) {
            $bond_holder = $user->bondHolders()->create([
                'name' => $name,
            ]);

            $set = collect($this->sets())->random();
            foreach ($this->setNumbers()[$set] as $number) {
                $set = $bond_holder->prizeBondSets()->create([
                    'user_id' => $bond_holder->user_id,
                    'name' => $number,
                ]);

                $loop = rand(1, 5);
                for ($i = 1; $i <= $loop; $i++) {
                    UserBondNumber::create([
                        'user_id' => $bond_holder->user_id,
                        'bond_holder_id' => $bond_holder->id,
                        'prize_bond_set_id' => $set->id,
                        'bond_number' => '0'.rand(100000, 999999),
                    ]);
                }
            }
        }
    }

    protected function bondHolders(): array
    {
        return [
            'Anika',
            'Golam Nabi',
            'Farhan',
            'Fardin',
            'Raisa',
        ];
    }

    protected function sets()
    {
        return [
            'numbers',
            'months',
            'sets',
        ];
    }

    protected function setNumbers()
    {
        return [
            'numbers' => ['01', '02', '03'],
            'months' => ['Jan', 'Feb', 'Mar'],
            'sets' => ['Set 1', 'Set 2', 'Set 3'],
        ];
    }
}
