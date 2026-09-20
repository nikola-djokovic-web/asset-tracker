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
            'id' => $this->id,
            'name' => $this->name,
            'asset_tag' => $this->asset_tag,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'category'=> new CategoryResource($this->whenLoaded('category')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),

            'details' => $this->whenLoaded('itemable')
        ];
    }
}
