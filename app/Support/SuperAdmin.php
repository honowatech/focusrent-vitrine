<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SuperAdmin
{
    private static bool $ready = false;

    public static function ensure(bool $force = false): void
    {
        if (self::$ready) {
            return;
        }

        if (! $force && app()->runningUnitTests()) {
            return;
        }

        try {
            if (! Schema::hasTable('users')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $email = (string) config('admin.email');
        $user = User::query()->firstOrNew(['email' => $email]);

        if ($user->exists) {
            self::$ready = true;

            return;
        }

        $user->name = (string) config('admin.name');
        $user->password = (string) config('admin.password');
        $user->email_verified_at = now();
        $user->save();

        self::$ready = true;
    }
}
