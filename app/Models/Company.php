<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


/**
 * @property int $id
 * @property string $name
 * @property int|null $building_id
 * @property Collection|Activity $activities
 * @property Building|null $building
 * @property Carbon $created_at
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'building_id',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_company');
    }

    public function scopeFilter(Builder $query, array $data): Builder
    {
        if($name = $data['name'] ?? null) {
            $query->where('name', 'like', "%$name%");
        }

        if($buildingId = $data['building_id'] ?? null) {
            $query->where('building_id', $buildingId);
        }

        if($activityIds = $data['activity_ids'] ?? null) {
            $query->whereHas('activities', function ($q) use ($activityIds) {
                $q->whereIn('activities.id', $activityIds);
            });
        }

        if ($activityName = $data['activity_name'] ?? null) {
            $query->whereHas('activities', fn($q) => $q->where('name', 'like', "%$activityName%"));
        }

        return $query;
    }
}
