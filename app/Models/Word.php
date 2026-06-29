<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Word extends Model
{
    protected $fillable = ['theme_id','text'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(WordTheme::class, 'theme_id');
    }
}
