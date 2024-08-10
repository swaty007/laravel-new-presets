<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Queries\Filters\FuzzyFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class ActivityService
{
    public function createLogActivity(Model|false $model, string $event, array $properties, array $oldProperties = []): void
    {
        $propertiesData = [
            'attributes' => $properties,
        ];
        if (!empty($oldProperties)) {
            $propertiesData['old'] = $oldProperties;
        }

        $activity = activity();
        if (!empty($model)) {
            $activity->performedOn($model);
        }
        //        $activity->tap(function (Activity $activity) use ($event) {
        //            $activity->subject_type = 'test';
        //            $activity->subject_id = 'test_id';
        //        });
        $activity->causedBy(Auth::user())
            ->withProperties($propertiesData)
            ->event($event)
            ->log($event);
    }

    /**
     * @param $userIds
     * @return Builder
     */
    public function getInitQuery($userIds = false): Builder
    {
        $initQuery = Activity::where(function (Builder $query) use ($userIds) {
            $query->where(function (Builder $query) use ($userIds) {
                $query->whereHasMorph('causer', [User::class]);
                $query->orWhere('causer_id', null);
            });

            if (!empty($userIds)) {
                $query->whereHasMorph('subject', [
                    User::class,
                ], function (Builder $query) use ($userIds) {
                    $query->getQuery()->wheres[0]['second'] = DB::raw($query->getQuery()->wheres[0]['second'] .' ::text');
                    $query->whereIn('id', $userIds);
                });
                $query->orWhereHasMorph('subject', [
//                    SettingsProperty::class,
//                    AdditionalData::class,
                ], function (Builder $query) use ($userIds) {
                    $query->getQuery()->wheres[0]['second'] = DB::raw($query->getQuery()->wheres[0]['second'] .' ::text');
                    $query->whereIn('user_id', $userIds);
                });
            }
        });

        return $initQuery;
    }

    /**
     * @param $initQuery
     * @return QueryBuilder
     */
    public function getIndexQuery($initQuery = false): QueryBuilder
    {
        if(empty($initQuery)) {
            $initQuery = $this->getInitQuery();
        }

        $activityQuery = QueryBuilder::for($initQuery)
            ->allowedFilters([
                AllowedFilter::custom('search', new FuzzyFilter(
                    'id',
                    'log_name',
                    'description',
                    'subject_type',
                    'subject_id',
                    'causer_type',
                    'causer_id',
                    'properties',
                    'event',
                    'created_at',
                    'updated_at',
                    'causer.email',
                )),
                AllowedFilter::exact('causer_type'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::exact('event'),
            ])
            ->defaultSort('-id')
            ->allowedSorts([
                'id',
                'log_name',
                'description',
                'subject_type',
                'subject_id',
                'causer_type',
                'causer_id',
                'properties',
                'event',
                'created_at',
                'updated_at',
                ]);

        return $activityQuery;
    }

    public function getPluckIndex(): Collection
    {
        return $this->getIndexQuery()->select(['id'])->pluck('id');
    }

    /**
     * @param int $per_page
     * @param $initQuery
     * @return AbstractPaginator
     */
    public function getIndexPagination(int $per_page = 15, $initQuery = false): AbstractPaginator
    {
        return $this->getIndexQuery($initQuery)
            ->with(['subject', 'causer'])
            ->select([
                'id',
                'log_name',
                'description',
                'subject_type',
                'subject_id',
                'causer_type',
                'causer_id',
                'properties',
                'event',
                'created_at',
                'updated_at',
            ])
            ->paginate($per_page)
            ->withQueryString();
    }

    public function getFilterOptions(): array
    {
        return Cache::remember('activity_options', 60 * 60 * 24, function () {
            return [
//            'users' => Activity::all()->map->only(['id', 'name'])->pluck('name', 'id'),
                'causer_type' => Activity::select('causer_type')->distinct()->pluck('causer_type')->filter()->values(),
                'subject_type' => Activity::select('subject_type')->distinct()->pluck('subject_type')->filter()->values(),
                'event' => Activity::select('event')->distinct()->pluck('event')->filter()->values(),
            ];
        });
    }
}
