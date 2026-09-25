<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = [
        'path',
        'page',
        'locale',
        'viewed_on',
        'visitor',
    ];

    protected $casts = [
        'viewed_on' => 'date',
    ];
}
