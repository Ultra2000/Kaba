<?php

namespace App\Providers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->customizeAuthEmails();
        $this->grantBadgeOnEmailVerification();
    }

    /**
     * Confirmer son adresse vaut le badge « Vérifié », sans passage par la
     * modération : c'est ce qui rend la démarche intéressante pour le vendeur,
     * et c'est la seule façon que ça tienne au-delà de quelques dizaines de membres.
     *
     * Un administrateur garde la main pour l'accorder ou le retirer autrement.
     */
    private function grantBadgeOnEmailVerification(): void
    {
        Event::listen(Verified::class, function (Verified $event) {
            $event->user->update(['is_verified' => true]);
        });
    }

    /**
     * Les e-mails d'authentification sont les premiers messages qu'un membre
     * reçoit de KABA : on les écrit avec nos mots plutôt que ceux de Laravel.
     */
    private function customizeAuthEmails(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $minutes = config('auth.passwords.users.expire', 60);

            return (new MailMessage)
                ->subject('Réinitialisez votre mot de passe KABA')
                ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
                ->line('Vous avez demandé à réinitialiser le mot de passe de votre compte KABA.')
                ->action('Choisir un nouveau mot de passe', url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)))
                ->line("Ce lien est valable {$minutes} minutes.")
                ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message : votre mot de passe reste inchangé.")
                ->salutation("À bientôt sur KABA,\nL'équipe KABA");
        });

        // Le compte fonctionne déjà entièrement : cet e-mail propose le badge,
        // il ne débloque rien. Le texte doit le dire, sinon il inquiète pour rien.
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Obtenez le badge « Vérifié » — KABA')
                ->greeting('Bienvenue sur KABA ' . ($notifiable->name ?? '') . ' !')
                ->line('Votre compte est déjà actif : vous pouvez publier vos livres, contacter des vendeurs et suivre vos demandes dès maintenant.')
                ->line('En confirmant cette adresse, vous obtenez en plus le badge « Vérifié » : il s\'affiche sur vos annonces et fait remonter vos ventes dans le catalogue.')
                ->action('Confirmer mon adresse', $url)
                ->line('Les dons et les échanges, eux, sont mis en avant pour tout le monde, badge ou pas.')
                ->line("Si vous n'avez pas créé de compte sur KABA, vous pouvez ignorer ce message : il ne se passera rien.")
                ->salutation("Bonne lecture,\nL'équipe KABA");
        });
    }
}
