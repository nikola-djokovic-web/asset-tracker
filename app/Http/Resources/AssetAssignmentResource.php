<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'asset_id'               => $this->asset_id,
            'assigned_to' => [
                'id'    => $this->assignedTo?->id,
                'name'  => $this->assignedTo?->name,
                'email' => $this->assignedTo?->email,
            ],
            'assigned_by' => [
                'id'    => $this->assignedBy?->id,
                'name'  => $this->assignedBy?->name,
                'email' => $this->assignedBy?->email,
            ],
            'assigned_at'           => $this->assigned_at?->toIso8601String(),
            'returned_at'           => $this->returned_at?->toIso8601String(),
            'notes'                 => $this->notes,
            'condition_on_checkout' => $this->condition_on_checkout,
            'condition_on_checkin'  => $this->condition_on_checkin,
            'is_active'             => is_null($this->returned_at),
            'created_at'            => $this->created_at?->toIso8601String(),
        ];
    }
}
