<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'asset_tag'       => $this->asset_tag,
            'status'          => $this->status,
            'category_id'     => $this->category_id,
            'organization_id' => $this->organization_id,
            'created_at'      => $this->created_at?->toIso8601String(),
            'category'        => new CategoryResource($this->whenLoaded('category')),
            'details'         => $this->whenLoaded('itemable'),
        ];
    }
}
