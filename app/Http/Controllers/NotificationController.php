<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()->notifications()->latest()->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read'       => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
