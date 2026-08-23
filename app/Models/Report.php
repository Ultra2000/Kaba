<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'reportable_type', 'reportable_id', 'reason', 'details', 'status'];

    public const REASONS = [
        'faux_livre'  => 'Faux livre / contrefaçon',
        'arnaque'     => "Suspicion d'arnaque",
        'prix_abusif' => 'Prix abusif',
        'offensant'   => 'Contenu offensant',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
