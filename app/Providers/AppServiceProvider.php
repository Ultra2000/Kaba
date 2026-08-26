<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirmez votre adresse e-mail — KABA')
                ->greeting('Bienvenue sur KABA ' . ($notifiable->name ?? '') . ' !')
                ->line('Il ne reste qu\'une étape : confirmer votre adresse e-mail pour activer votre compte.')
                ->action('Confirmer mon adresse', $url)
                ->line('Vous pourrez ensuite publier vos livres, contacter des vendeurs et suivre vos demandes.')
                ->line("Si vous n'avez pas créé de compte sur KABA, vous pouvez ignorer ce message.")
                ->salutation("Bonne lecture,\nL'équipe KABA");
        });
    }
}
