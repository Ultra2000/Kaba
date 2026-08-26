<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Vérifie que l'envoi d'e-mails fonctionne réellement.
 *
 * À lancer juste après avoir renseigné le SMTP en production :
 *   php artisan kaba:mail-test votre@adresse.com
 */
class MailTest extends Command
{
    protected $signature = 'kaba:mail-test {email : Adresse qui doit recevoir le message}';

    protected $description = "Envoie un e-mail de test pour valider la configuration SMTP";

    public function handle(): int
    {
        $to = $this->argument('email');

        $this->line('');
        $this->line('  Configuration utilisée');
        $this->table(['Paramètre', 'Valeur'], [
            ['Transport', config('mail.default')],
            ['Hôte', config('mail.mailers.smtp.host') ?: '—'],
            ['Port', config('mail.mailers.smtp.port') ?: '—'],
            ['Chiffrement', config('mail.mailers.smtp.encryption') ?: '—'],
            ['Utilisateur', config('mail.mailers.smtp.username') ?: '—'],
            ['Expéditeur', config('mail.from.address') . ' (' . config('mail.from.name') . ')'],
        ]);

        if (config('mail.default') === 'log') {
            $this->warn("  ⚠  MAIL_MAILER=log : le message ira dans storage/logs, pas dans une boîte mail.");
            $this->warn('     Renseignez le SMTP dans .env puis relancez « php artisan config:cache ».');
            $this->line('');
        }

        try {
            Mail::raw(
                "Ceci est un e-mail de test envoyé depuis KABA.\n\n"
                . "Si vous le recevez, la configuration SMTP est correcte : "
                . "les réinitialisations de mot de passe et les confirmations d'adresse fonctionneront.\n\n"
                . 'Envoyé le ' . now()->format('d/m/Y à H:i') . '.',
                fn ($message) => $message->to($to)->subject('Test d\'envoi — KABA')
            );
        } catch (\Throwable $e) {
            $this->line('');
            $this->error('  ✗  Échec de l\'envoi : ' . $e->getMessage());
            $this->line('');
            $this->line('  Pistes habituelles :');
            $this->line('   • identifiants SMTP incorrects (utilisateur = adresse complète en général) ;');
            $this->line('   • mauvais couple port/chiffrement (465 avec ssl, ou 587 avec tls) ;');
            $this->line('   • expéditeur MAIL_FROM_ADDRESS non autorisé par l\'hébergeur ;');
            $this->line('   • port sortant bloqué par l\'hébergement.');
            $this->line('');

            return self::FAILURE;
        }

        $this->line('');
        $this->info("  ✓  Message remis au serveur pour {$to}.");
        $this->line('     Vérifiez la boîte de réception (et les indésirables).');
        $this->line('');

        return self::SUCCESS;
    }
}
