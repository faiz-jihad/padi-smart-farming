<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type ?? 'system',
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data ?? $this->data_json ?? [],
            'read_at' => $this->read_at ? (is_string($this->read_at) ? $this->read_at : $this->read_at->toIso8601String()) : null,
            'is_read' => $this->read_at !== null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : now()->toIso8601String(),
        ];
    }
}