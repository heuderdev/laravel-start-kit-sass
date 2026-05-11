<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->tenant_role ?? $this->pivot->role,
            'is_default' => (bool) $this->pivot->is_default,
            'status' => $this->pivot->status,
            'joined_at' => $this->pivot->joined_at,
        ];
    }
}
