<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function storeForListing(Request $request, Listing $listing): RedirectResponse
    {
        $data = $request->validate([
            'reason'  => 'required|in:' . implode(',', array_keys(Report::REASONS)),
            'details' => 'nullable|string|max:1000',
        ]);

        $listing->morphMany(Report::class, 'reportable')->create([
            'reporter_id' => Auth::id(),
            'reason'      => $data['reason'],
            'details'     => $data['details'] ?? null,
        ]);

        return back()->with('success', 'Signalement envoyé. Notre équipe va l’examiner.');
    }
}
