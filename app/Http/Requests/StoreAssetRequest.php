<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'max:255'],
           'asset_tag' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('assets', 'asset_tag')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })
            ],
            'status'          => ['required', 'string', 'in:active,inactive,maintenance'],
            'category_id'     => ['required', 'exists:categories,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'type'            => ['required', 'string', 'in:hardware,license'],

            'details.serial_number' => ['required_if:type,hardware', 'nullable', 'string', 'max:255'],
            'details.specs'         => ['nullable', 'array'],

            'details.license_key' => ['required_if:type,license', 'nullable', 'string', 'max:255'],
            'details.seats'       => ['required_if:type,license', 'integer', 'min:1'],
            'details.expires_at'  => ['nullable', 'date'],
        ];
    }
}
