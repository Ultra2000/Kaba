<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuthEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_interface_messages_are_in_french(): void
    {
        $validator = Validator::make(['name' => ''], ['name' => 'required']);
        $validator->fails();

        // Le site est francophone : les erreurs de formulaire doivent l'être aussi.
        $this->assertSame('Le champ nom est obligatoire.', $validator->errors()->first('name'));
    }

    public function test_password_reset_email_is_branded_and_in_french(): void
    {
        $user = User::factory()->create(['name' => 'Aïcha K.']);

        $mail = (new ResetPassword('jeton'))->toMail($user);

        $this->assertSame('Réinitialisez votre mot de passe KABA', $mail->subject);
        $this->assertSame('Choisir un nouveau mot de passe', $mail->actionText);
        $this->assertStringContainsString('Aïcha K.', $mail->greeting);

        $rendered = $mail->render();
        $this->assertStringContainsString('KABA', $rendered);
        $this->assertStringContainsString('#7C3AED', $rendered, 'Le gabarit n\'utilise pas la couleur de la marque.');
        // Aucun reliquat du texte anglais de Laravel.
        $this->assertStringNotContainsString('You are receiving this email', $rendered);
        $this->assertStringNotContainsString('Reset Password', $rendered);
    }

    public function test_verification_email_is_branded_and_in_french(): void
    {
        $user = User::factory()->create(['name' => 'Koffi']);

        $mail = (new VerifyEmail())->toMail($user);

        $this->assertSame('Confirmez votre adresse e-mail — KABA', $mail->subject);
        $this->assertSame('Confirmer mon adresse', $mail->actionText);
        $this->assertStringContainsString('Bienvenue sur KABA', $mail->greeting);
        $this->assertStringNotContainsString('Verify Email Address', $mail->render());
    }

    public function test_forgotten_password_request_sends_the_email(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
