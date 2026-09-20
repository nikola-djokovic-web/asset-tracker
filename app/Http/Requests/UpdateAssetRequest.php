<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        $assetId = $this->route('asset')?->id ?? $this->route('asset');
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'asset_tag'       => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('assets', 'asset_tag')
                    ->where('tenant_id', $tenantId)
                    ->ignore($assetId)
            ],
            'status'          => ['sometimes', 'required', 'string', 'in:active,inactive,maintenance'],
            'category_id'     => ['sometimes', 'required', 'exists:categories,id'],
            'organization_id' => ['sometimes', 'required', 'exists:organizations,id'],
        ];
    }
}
