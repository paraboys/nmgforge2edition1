<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'stats' => $this['stats'],
            'recent_activity' => ActivityLogResource::collection($this['recent_activity']),
        ];
    }
}
