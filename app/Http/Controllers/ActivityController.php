<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\IndexActivityRequest;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /** @var ActivityService */
    protected ActivityService $activityService;
    public function __construct(
        ActivityService $activityService,
    ) {
        $this->activityService = $activityService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(IndexActivityRequest $request): Response | JsonResponse
    {
        if ($request->wantsJson() && $request->get('bulk_select_all')) {
            return response()->json($this->activityService->getPluckIndex());
        }

        $activities = $this->activityService->getIndexPagination($request->get('per_page', 100));

        return Inertia::render('Activity/Index', [
            'activities' => $activities,
            'filterOptions' => $this->activityService->getFilterOptions(),
        ]);
    }

    public function indexUser(IndexActivityRequest $request, User $user): Response | JsonResponse
    {
        if ($request->wantsJson() && $request->get('bulk_select_all')) {
            return response()->json($this->activityService->getPluckIndex());
        }

        $activities = $this->activityService
            ->getIndexPagination(
                $request->get('per_page', 100),
                $this->activityService->getInitQuery([$user->id])
            );

        return Inertia::render('Activity/UserActivity', [
            'activities' => $activities,
            'filterOptions' => $this->activityService->getFilterOptions(),
            'user' => $user,
        ]);
    }
}
