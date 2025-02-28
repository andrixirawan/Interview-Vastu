<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function routesFrom(): HasMany
    {
        return $this->hasMany(Route::class, 'from_city_id');
    }

    public function routesTo(): HasMany
    {
        return $this->hasMany(Route::class, 'to_city_id');
    }
} 