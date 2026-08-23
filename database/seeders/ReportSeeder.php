<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporters = User::where('email', 'like', 'acheteur%@kaba.bj')->pluck('id')->all();
        if (empty($reporters)) {
            return;
        }

        $listings = Listing::inRandomOrder()->take(4)->get();
        $reasons = ['faux_livre', 'arnaque', 'prix_abusif', 'offensant'];

        foreach ($listings as $i => $listing) {
            $listing->morphMany(Report::class, 'reportable')->create([
                'reporter_id' => $reporters[$i % count($reporters)],
                'reason'      => $reasons[$i % count($reasons)],
                'details'     => 'Signalement de démonstration.',
                'status'      => 'open',
            ]);
        }
    }
}
