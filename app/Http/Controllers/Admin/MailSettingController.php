<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Support\MailConfigurator;
use Illuminate\Http\Request;

class MailSettingController extends Controller
{
    public function edit()
    {
        return view('admin.mail', [
            'setting' => MailSetting::current(),
            'hasPassword' => filled(MailSetting::current()->password) || filled(config('mail.mailers.smtp.password')),
        ]);
    }

    public function update(Request $request, MailConfigurator $mail)
    {
        $setting = $this->store($request);

        if ($request->input('intent') !== 'test') {
            return redirect()->route('admin.mail')->with('success', 'Paramètres enregistrés.');
        }

        try {
            $mail->sendTest($setting);
        } catch (\Throwable $exception) {
            return redirect()->route('admin.mail')->withInput($request->except('password'))->with(
                'error',
                'Paramètres enregistrés, mais l’e-mail de test a échoué : '.$exception->getMessage()
            );
        }

        return redirect()->route('admin.mail')->with(
            'success',
            'E-mail de test envoyé à '.$setting->reception_email.'.'
        );
    }

    protected function store(Request $request): MailSetting
    {
        $data = $request->validate([
            'reception_email' => ['required', 'email', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = MailSetting::query()->first() ?? new MailSetting();
        $setting->fill([
            'reception_email' => $data['reception_email'],
            'from_address' => $data['from_address'],
            'from_name' => $data['from_name'],
            'host' => $data['host'],
            'port' => $data['port'],
            'encryption' => $data['encryption'] ?: null,
            'username' => $data['username'] ?: null,
        ]);

        if ($request->filled('password')) {
            $setting->password = $data['password'];
        }

        $setting->save();

        return $setting;
    }
}
