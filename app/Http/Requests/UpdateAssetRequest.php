<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        
        // Bezbedno izvlačenje ID-ja bez obzira da li je Route Model Binding vratio Model ili ULID string
        $asset = $this->route('asset');
        $assetId = is_object($asset) ? $asset->id : $asset;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'asset_tag' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('assets', 'asset_tag')
                    ->where('tenant_id', $tenantId)
                    ->ignore($assetId),
            ],
            'status'          => ['sometimes', 'required', 'string', 'in:active,assigned,maintenance,retired,inactive'],
            'category_id'     => ['sometimes', 'required', 'exists:categories,id'],
            'organization_id' => ['sometimes', 'required', 'exists:organizations,id'],

            // Neophodno da bi $request->validated() obuhvatio ugnježđene detalje!
            'details'               => ['sometimes', 'array'],
            'details.serial_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'details.specs'         => ['sometimes', 'nullable', 'array'],
        ];
    }
}