<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'building' => $this->whenLoaded('building', fn() => [
                'id' => $this->building->id,
                'address' => $this->building->address,
                'latitude' => $this->building->latitude,
                'longitude' => $this->building->longitude,
            ]),
            'activities' => $this->whenLoaded('activities', fn() => $this->activities->map(fn($activity) => [
                'id' => $activity->id,
                'name' => $activity->name,
            ])),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
