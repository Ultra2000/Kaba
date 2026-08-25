<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use App\Notifications\KabaNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /** Date de mise à jour affichée sur les pages légales. */
    private const LEGAL_UPDATED = '24 août 2026';

    public function about(): Response
    {
        // Chiffres réels de la plateforme, rafraîchis toutes les heures.
        $stats = Cache::remember('about.stats', 3600, fn () => [
            'listings'  => Listing::where('status', 'active')->count(),
            'members'   => User::where('role', '!=', 'admin')->count(),
            'donations' => Listing::where('status', 'active')->where('type', 'don')->count(),
            'exchanges' => Listing::where('status', 'active')->where('type', 'echange')->count(),
        ]);

        return Inertia::render('About', ['stats' => $stats]);
    }

    public function faq(): Response
    {
        return Inertia::render('Faq');
    }

    public function security(): Response
    {
        return Inertia::render('Security');
    }

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', ['updated' => self::LEGAL_UPDATED]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', ['updated' => self::LEGAL_UPDATED]);
    }

    public function contact(Request $request): Response
    {
        return Inertia::render('Contact', ['sent' => (bool) $request->session()->get('contact_sent')]);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|in:question,probleme,compte,partenariat,autre',
            'message' => 'required|string|max:2000',
        ]);

        // Les administrateurs sont notifiés dans leur espace.
        $subjects = [
            'question'    => 'Question',
            'probleme'    => 'Problème signalé',
            'compte'      => 'Compte',
            'partenariat' => 'Partenariat / presse',
            'autre'       => 'Autre',
        ];

        foreach (User::where('role', 'admin')->get() as $admin) {
            $admin->notify(new KabaNotification([
                'kind'    => 'contact',
                'icon'    => 'fa-envelope',
                'color'   => 'brand',
                'message' => "[{$subjects[$data['subject']]}] {$data['name']} ({$data['email']}) : "
                    . mb_strimwidth($data['message'], 0, 120, '…'),
                'url'     => '/notifications',
            ]));
        }

        return back()->with('contact_sent', true);
    }
}
