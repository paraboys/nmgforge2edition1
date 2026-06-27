<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action->value,
            'meta' => $this->meta,
            'actor' => new UserResource($this->whenLoaded('actor')),
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
