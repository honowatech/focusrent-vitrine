<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Mail\TestMail;
use App\Models\MailSetting;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\MailConfigurator;
use App\Support\SuperAdmin;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminBackofficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_creates_the_admin_account(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Créer le compte administrateur', false);

        $this->post('/admin/setup', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect('/admin');

        $this->get('/admin')->assertOk()->assertSee('Pages vues', false);
    }

    public function test_superadmin_is_created_and_can_sign_in(): void
    {
        SuperAdmin::ensure(true);

        $this->assertDatabaseHas('users', ['email' => 'admin@focusrent.cm']);

        $login = $this->get('/admin/login');
        $login->assertOk()
            ->assertSee('admin@focusrent.cm', false)
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');

        $this->post('/admin/login', [
            'email' => 'admin@focusrent.cm',
            'password' => config('admin.password'),
        ])->assertRedirect('/admin');
    }

    public function test_expired_login_session_returns_to_the_form(): void
    {
        $this->get('/admin/login');

        $request = \Illuminate\Http\Request::create('/admin/login', 'POST', [
            'email' => 'admin@focusrent.cm',
            'password' => 'mauvais',
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $response = app(\App\Exceptions\Handler::class)->render(
            $request,
            new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/login', (string) $response->headers->get('Location'));
        $this->get('/admin/login')->assertOk()->assertSee('La session a expiré', false);
    }

    public function test_visits_are_grouped_by_page(): void
    {
        $user = User::factory()->create();

        $this->get('/');
        $this->get('/tarifs');
        $this->get('/tarifs');

        $this->assertDatabaseCount('page_views', 3);

        $this->actingAs($user)
            ->get('/admin?period=today')
            ->assertOk()
            ->assertSee('/tarifs', false)
            ->assertSee('Tarifs', false);
    }

    public function test_contact_form_is_stored_and_visible_in_the_backoffice(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);
        Mail::fake();
        $user = User::factory()->create();

        $this->from('/contact')->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'phone' => '+237600000000',
            'message' => 'Je veux une démo.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'awa@example.com',
            'message' => 'Je veux une démo.',
        ]);
        Mail::assertNothingSent();

        $this->actingAs($user)->get('/admin/messages')->assertOk()->assertSee('Awa', false);
        $this->actingAs($user)->get('/admin/messages/1')
            ->assertOk()
            ->assertSee('Je veux une démo.', false);
    }

    public function test_contact_form_notifies_reception_email_when_mail_is_configured(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);
        Mail::fake();
        $this->storeMailSettings();

        $this->from('/contact')->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'phone' => '+237600000000',
            'message' => 'Je veux une démo.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact');

        Mail::assertSent(ContactMail::class, function (ContactMail $mail) {
            return $mail->hasTo('reception@focusrent.cm');
        });
    }

    public function test_contact_form_succeeds_even_when_the_email_fails(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);
        $this->storeMailSettings();
        $this->mock(MailConfigurator::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendContact')->once()->andThrow(new \RuntimeException('SMTP indisponible'));
        });

        $this->from('/contact')->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'phone' => '+237600000000',
            'message' => 'Je veux une démo.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'awa@example.com',
            'message' => 'Je veux une démo.',
        ]);
    }

    public function test_contact_form_shows_failure_when_database_and_email_both_fail(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);
        Schema::drop('contact_messages');
        $this->storeMailSettings();
        $this->mock(MailConfigurator::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendContact')->once()->andThrow(new \RuntimeException('SMTP indisponible'));
        });

        $this->from('/contact')->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'phone' => '+237600000000',
            'message' => 'Je veux une démo.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact')->assertSessionHas('error')->assertSessionHas('_old_input');
    }

    public function test_contact_form_succeeds_when_only_the_database_fails(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);
        Schema::drop('contact_messages');
        $this->storeMailSettings();
        $this->mock(MailConfigurator::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendContact')->once();
        });

        $this->from('/contact')->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'phone' => '+237600000000',
            'message' => 'Je veux une démo.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact')->assertSessionHas('success');
    }

    public function test_site_mode_can_be_updated_from_the_backoffice(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/mode')
            ->assertOk()
            ->assertSee('Mode du site', false)
            ->assertSee('Maintenance', false);

        $this->actingAs($user)->post('/admin/mode', ['mode' => 'maintenance'])
            ->assertRedirect('/admin/mode')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', ['mode' => 'maintenance']);

        $this->actingAs($user)->post('/admin/mode', ['mode' => 'production']);
        $this->assertDatabaseHas('site_settings', ['mode' => 'production']);
    }

    public function test_maintenance_mode_shows_landing_page_to_visitors_only(): void
    {
        SiteSetting::query()->create(['mode' => 'maintenance']);
        $user = User::factory()->create();

        $this->get('/')->assertStatus(503)->assertSee('Site en maintenance', false);
        $this->get('/en/contact')->assertStatus(503)->assertSee('Site under maintenance', false);

        $this->actingAs($user)->get('/')->assertOk();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_development_mode_disables_recaptcha(): void
    {
        config(['services.recaptcha.skip' => false]);
        SiteSetting::query()->create(['mode' => 'development']);

        $this->from('/contact')->post('/contact', [
            'name' => 'Dev',
            'phone' => '+237600000003',
            'message' => 'Test en mode développement sans jeton reCAPTCHA.',
        ])->assertRedirect('/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['name' => 'Dev']);
    }

    public function test_contact_form_rejects_a_missing_recaptcha_in_production(): void
    {
        config(['services.recaptcha.skip' => false]);
        SiteSetting::query()->create(['mode' => 'production']);
        Http::fake([
            'www.google.com/*' => Http::response(['success' => false]),
        ]);

        $this->from('/contact')->post('/contact', [
            'name' => 'Bot',
            'phone' => '+237600000012',
            'message' => 'Soumission sans le champ reCAPTCHA.',
        ])->assertRedirect('/contact')->assertSessionHas('errors');

        $this->assertDatabaseMissing('contact_messages', ['name' => 'Bot']);
    }

    public function test_contact_page_renders_recaptcha_widget_only_when_active(): void
    {
        config(['services.recaptcha.skip' => false, 'services.recaptcha.key' => 'site-key']);

        SiteSetting::query()->create(['mode' => 'development']);
        $this->get('/contact')->assertOk()->assertDontSee('g-recaptcha');

        SiteSetting::query()->update(['mode' => 'production']);
        $this->get('/contact')->assertOk()->assertSee('g-recaptcha');
    }

    protected function storeMailSettings(): void
    {
        MailSetting::query()->create([
            'reception_email' => 'reception@focusrent.cm',
            'from_address' => 'noreply@focusrent.cm',
            'from_name' => 'Focus Rent',
            'host' => 'smtp.focusrent.cm',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'noreply@focusrent.cm',
            'password' => 'secret',
        ]);
    }

    public function test_contact_form_accepts_a_missing_email(): void
    {
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);

        $this->from('/contact')->post('/contact', [
            'name' => 'Bozi',
            'phone' => '+237600000001',
            'message' => 'Pas d\'e-mail, juste le téléphone.',
            'g-recaptcha-response' => 'token',
        ])->assertRedirect('/contact');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Bozi',
            'email' => null,
        ]);
    }

    public function test_contact_form_skips_recaptcha_verification_when_configured(): void
    {
        config(['services.recaptcha.skip' => true]);

        $this->from('/contact')->post('/contact', [
            'name' => 'Local',
            'phone' => '+237600000002',
            'message' => 'Test local sans jeton reCAPTCHA.',
        ])->assertRedirect('/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Local',
        ]);
    }

    public function test_mail_settings_can_be_saved_and_tested(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/mail', [
            'intent' => 'test',
            'reception_email' => 'inbox@example.com',
            'from_address' => 'from@example.com',
            'from_name' => 'Focus Rent',
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'from@example.com',
            'password' => 'secret',
        ])->assertRedirect('/admin/mail');

        Mail::assertSent(TestMail::class, function (TestMail $mail) {
            return $mail->hasTo('inbox@example.com');
        });

        $stored = MailSetting::query()->first();
        $this->assertSame('inbox@example.com', $stored->reception_email);
        $this->assertSame('secret', $stored->password);
        $this->assertNotSame('secret', $stored->getRawOriginal('password'));
    }

    public function test_robots_hides_the_backoffice(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin', false);
    }

    public function test_admin_is_an_installable_pwa(): void
    {
        $manifest = $this->get('/admin/manifest.webmanifest');
        $manifest->assertOk();
        $manifest->assertHeader('Content-Type', 'application/manifest+json');
        $body = $manifest->json();
        $this->assertSame('standalone', $body['display']);
        $this->assertSame('/admin', $body['start_url']);
        $this->assertSame('/admin', $body['scope']);
        $this->assertCount(3, $body['icons']);

        $worker = $this->get('/admin/sw.js');
        $worker->assertOk();
        $worker->assertHeader('Service-Worker-Allowed', '/admin');
        $worker->assertSee('focusrent-admin-v2', false);
        $worker->assertSee("pathname.startsWith('/admin')", false);
    }
}
