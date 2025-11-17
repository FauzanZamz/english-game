<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachedDef extends Model
{
    protected $primaryKey = 'word';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['word','definitions','wiki_extract','wiki_image','cached_at'];
    public $timestamps = false;
}
