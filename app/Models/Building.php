<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $note
 * @property Collection|null $companies
 */
class Building extends Model
{
     use HasFactory;

    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'note',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
