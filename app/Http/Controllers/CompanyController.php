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

    /**
     * Get paginated list of companies.
     *
     * @group Company management
     *
     * @queryParam per_page int The number of items per page. Default is 15. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Company 1",
     *       "building": {
     *           "id": 1,
     *           "address": "Some address"
     *       },
     *       "activities": [
     *           {"id": 1, "name": "Activity 1"}
     *       ]
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $companies = Company::query()
            ->with('building')
            ->filter($request->all())
            ->paginate($request->integer('per_page', 15));

        return CompanyResource::collection($companies);
    }


    /**
     * Get companies by activity (including child activities).
     *
     * @group Company management
     *
     * @urlParam activity int required The ID of the activity Example: 1
     * @queryParam per_page int The number of items per page. Default is 15. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Company 1",
     *       "building": {...},
     *       "activities": [...]
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function activityIndex(Request $request, Activity $activity): AnonymousResourceCollection
    {
        $activityIds = $this->getActivityTree($activity);

        $companies = Company::query()
            ->with(['building', 'activities'])
            ->filter($request->all())
            ->whereHas('activities', fn (Builder $q) => $q->whereIn('activities.id', $activityIds));

        return CompanyResource::collection(
            $companies->paginate($request->integer('per_page', 15))
        );
    }


    /**
     * Get companies nearest to a given location within a radius.
     *
     * @group Company management
     *
     * @queryParam latitude float required Latitude Example: 37.7749
     * @queryParam longitude float required Longitude Example: -122.4194
     * @queryParam radius float The search radius in km. Default is 1. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Company 1",
     *       "building": {...},
     *       "activities": [...]
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function nearest(NearestCompanyRequest $request): AnonymousResourceCollection
    {
        $lat = $request->float('latitude');
        $lng = $request->float('longitude');
        $radius = $request->float('radius', 1); // км

        $companies = Company::query()
            ->with(['building', 'activities'])
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

    /**
     * Get a single company by ID.
     *
     * @group Company management
     *
     * @urlParam company int required The ID of the company Example: 1
     *
     * @response 200 {
     *   "data": {
     *       "id": 1,
     *       "name": "Company 1",
     *       "building": {...},
     *       "activities": [...]
     *   }
     * }
     */
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
