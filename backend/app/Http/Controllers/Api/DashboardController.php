<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardStatsResource;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index()
    {
        $user = auth()->user();
        $stats = $this->dashboardService->getStats($user);
        $activity = $this->dashboardService->getRecentActivity($user);

        return new DashboardStatsResource([
            'stats' => $stats,
            'recent_activity' => $activity,
        ]);
    }
}
