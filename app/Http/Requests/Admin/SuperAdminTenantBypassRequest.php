<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SuperAdminTenantBypassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('super-admin') === true
            || $this->user()?->hasRole('super-admin') === true;
    }

    public function rules(): array
    {
        return [
            'bypass_plan_limits' => ['required', 'boolean'],
            'bypass_plan_limits_data_limite' => [
                'nullable',
                'date',
                'required_if:bypass_plan_limits,1',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bypass_plan_limits' => filter_var(
                $this->input('bypass_plan_limits'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false,
        ]);
    }

    public function messages(): array
    {
        return [
            'bypass_plan_limits_data_limite.required_if' =>
            'A data limite é obrigatória quando o bypass estiver ativo.',
        ];
    }
}
