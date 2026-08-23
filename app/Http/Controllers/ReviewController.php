<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if(Auth::id() === $user->id, 403, 'Vous ne pouvez pas vous évaluer.');

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['author_id' => Auth::id(), 'seller_id' => $user->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        $user->recalcRating();

        return back()->with('success', 'Merci pour votre avis !');
    }
}
