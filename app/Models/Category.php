<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'photo',
        'photo_white',
    ];

    //mutator
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    //relations
    public function homeServices(): HasMany
    {
        return $this->hasMany(HomeService::class);
    }

    public function popularServices() 
    {
        return $this->hasMany(HomeService::class)
            ->where('is_popular', true)
            ->orderBy('created_at', 'desc');
    }
}
