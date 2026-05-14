<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingSetupWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_name' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_name.required' => 'O nome do workspace é obrigatório.',
            'workspace_name.min'      => 'O nome deve ter pelo menos 2 caracteres.',
            'workspace_name.max'      => 'O nome não pode ter mais de 100 caracteres.',
        ];
    }
}
