<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\KabaNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'vendeur1@kaba.bj')->first();
        if (! $user) {
            return;
        }

        $items = [
            ['icon' => 'fa-sack-dollar', 'color' => 'text-green-500 bg-green-50', 'message' => "Vente confirmée — « L'Étranger » a été acheté.", 'url' => '/dashboard'],
            ['icon' => 'fa-comment', 'color' => 'text-brand-600 bg-brand-50', 'message' => 'Jean-Marc H. vous a envoyé un message.', 'url' => '/dashboard'],
            ['icon' => 'fa-magnifying-glass', 'color' => 'text-green-600 bg-green-50', 'message' => "Le livre recherché « Atomic Habits » vient d'être publié !", 'url' => '/explorer'],
            ['icon' => 'fa-arrow-down', 'color' => 'text-orange-500 bg-orange-50', 'message' => "Baisse de prix sur « Sapiens » : 7 000 F → 6 000 F.", 'url' => '/explorer'],
            ['icon' => 'fa-heart', 'color' => 'text-red-500 bg-red-50', 'message' => '3 personnes ont ajouté vos livres en favori.', 'url' => '/dashboard'],
        ];

        foreach ($items as $item) {
            $user->notify(new KabaNotification($item));
        }

        // Étale les dates et marque les plus anciennes comme lues
        $all = $user->notifications()->latest()->get();
        foreach ($all as $i => $n) {
            $n->created_at = now()->subHours($i * 20);
            if ($i >= 3) {
                $n->read_at = now();
            }
            $n->save();
        }
    }
}
