<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity'
    ];

    protected $casts = [
        'capacity' => 'integer'
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
} 