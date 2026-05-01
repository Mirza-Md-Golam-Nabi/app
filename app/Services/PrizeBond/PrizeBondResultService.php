<?php

namespace App\Services\PrizeBond;

use App\Models\PrizeBondDraw;
use App\Models\PrizeBondResult;
use App\Models\UserBondNumber;
use Illuminate\Support\Facades\DB;

class PrizeBondResultService
{
    public function process(PrizeBondDraw $draw): void
    {
        $winningNumbers = $this->getWinningNumbers($draw);

        if ($winningNumbers->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($draw, $winningNumbers) {
            $this->deleteOldResults($draw);
            $this->insertNewResults($draw, $winningNumbers);
        });
    }

    private function getWinningNumbers(PrizeBondDraw $draw)
    {
        return $draw->winningNumbers()
            ->pluck('prize_rank', 'winning_number');
    }

    private function deleteOldResults(PrizeBondDraw $draw): void
    {
        PrizeBondResult::where('draw_id', $draw->id)->delete();
    }

    private function insertNewResults(PrizeBondDraw $draw, $winningNumbers): void
    {
        UserBondNumber::query()
            ->whereIn('bond_number', $winningNumbers->keys())
            ->chunkById(50, function ($matchedBonds) use ($draw, $winningNumbers) {
                $inserts = $matchedBonds->map(fn ($bond) => [
                    'draw_id' => $draw->id,
                    'user_id' => $bond->user_id,
                    'bond_number' => $bond->bond_number,
                    'prize_rank' => $winningNumbers[$bond->bond_number],
                    'is_notified' => false,
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                DB::table('prize_bond_results')->insert($inserts);
            });
    }
}
