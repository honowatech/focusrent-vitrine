<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public const MODES = [
        'production' => 'Production',
        'maintenance' => 'Maintenance',
        'development' => 'Développement',
    ];

    protected $fillable = ['mode'];

    public static function current(): self
    {
        return static::query()->first() ?? new static(['mode' => 'production']);
    }

    public function isMaintenance(): bool
    {
        return $this->mode === 'maintenance';
    }

    public function isDevelopment(): bool
    {
        return $this->mode === 'development';
    }
}
