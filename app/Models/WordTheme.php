<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordTheme extends Model
{
    protected $fillable = ['slug','name'];

    public function words(): HasMany
    {
        return $this->hasMany(Word::class, 'theme_id');
    }
}
