<?php

namespace App\Support;

use App\Mail\ContactMail;
use App\Mail\TestMail;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;

class MailConfigurator
{
    public function apply(MailSetting $setting): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.from.address' => $setting->from_address,
            'mail.from.name' => $setting->from_name,
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $setting->host,
            'mail.mailers.smtp.port' => $setting->port,
            'mail.mailers.smtp.encryption' => $setting->encryption ?: null,
            'mail.mailers.smtp.username' => $setting->username,
            'mail.mailers.smtp.password' => $setting->resolvedPassword(),
            'mail.mailers.smtp.timeout' => 15,
        ]);

        Mail::purge('smtp');
    }

    public function sendTest(MailSetting $setting): void
    {
        $this->apply($setting);

        Mail::mailer('smtp')->to($setting->reception_email)->send(new TestMail());
    }

    public function sendContact(MailSetting $setting, array $data): void
    {
        $this->apply($setting);

        Mail::mailer('smtp')->to($setting->reception_email)->send(new ContactMail($data));
    }
}
