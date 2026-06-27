<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'tags' => $this->tags,
            'requester' => new UserResource($this->whenLoaded('requester')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
