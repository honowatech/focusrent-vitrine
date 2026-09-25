<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'reception_email',
        'from_address',
        'from_name',
        'host',
        'port',
        'encryption',
        'username',
        'password',
    ];

    protected $casts = [
        'port' => 'integer',
        'password' => 'encrypted',
    ];

    public static function current(): self
    {
        $stored = static::query()->first();

        if ($stored) {
            return $stored;
        }

        return new static([
            'reception_email' => config('mail.from.address'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => (int) config('mail.mailers.smtp.port', 587),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
        ]);
    }

    public function resolvedPassword(): ?string
    {
        if (filled($this->password)) {
            return $this->password;
        }

        $fallback = config('mail.mailers.smtp.password');

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }
}
