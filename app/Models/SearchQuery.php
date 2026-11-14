<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * Journal anonyme des recherches : aucune adresse IP, purge après 13 mois.
 */
class SearchQuery extends Model
{
    use Prunable;

    public const RETENTION_MONTHS = 13;

    protected $fillable = [
        'query',
        'normalized',
        'locale',
        'source',
        'token',
        'results_count',
        'clicked_id',
        'clicked_type',
        'clicked_position',
        'visitor_id',
        'is_bot',
        'searched_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'clicked_position' => 'integer',
        'is_bot' => 'boolean',
        'searched_at' => 'datetime',
    ];

    public function prunable(): Builder
    {
        return static::query()->where('searched_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }

    public function scopeHumans(Builder $query): Builder
    {
        return $query->where('is_bot', false);
    }
}
