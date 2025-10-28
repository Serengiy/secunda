<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organisation\NearestCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Activity;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $companies = Company::query()
            ->with('building')
            ->filter($request->all())
            ->paginate($request->integer('per_page', 15));

        return CompanyResource::collection($companies);
    }

    public function activityIndex(Request $request, Activity $activity): AnonymousResourceCollection
    {
        $activityIds = $this->getActivityTree($activity);

        $companies = Company::query()
            ->filter($request->all())
            ->whereHas('activities', fn (Builder $q) => $q->whereIn('activities.id', $activityIds));

        return CompanyResource::collection(
            $companies->paginate($request->integer('per_page', 15))
        );
    }

    public function nearest(NearestCompanyRequest $request): AnonymousResourceCollection
    {
        $lat = $request->float('latitude');
        $lng = $request->float('longitude');
        $radius = $request->float('radius', 1); // км

        $companies = Company::query()
            ->whereHas('building', function (Builder $query) use ($lat, $lng, $radius) {
                // Формула гаверсина (Haversine) для расчёта расстояния
                $query->whereRaw("
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    ) <= ?
                ", [$lat, $lng, $lat, $radius]);
            });

        return CompanyResource::collection($companies->paginate());
    }

    public function show(Company $company): CompanyResource
    {
        $company->load(['building', 'activities']);
        return new CompanyResource($company);
    }

    protected function getActivityTree(Activity $activity, int $maxDepth = 3, int $current = 1): array
    {
        $ids = [$activity->id];

        if ($current < $maxDepth) {
            foreach ($activity->children as $child) {
                $ids = array_merge($ids, $this->getActivityTree($child, $maxDepth, $current + 1));
            }
        }

        return $ids;
    }

}
