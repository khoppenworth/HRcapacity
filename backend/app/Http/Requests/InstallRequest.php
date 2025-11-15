<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesDefinition();
    }

    /**
     * Provide the reusable validation rules for installation requests.
     */
    public static function rulesDefinition(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_slug' => ['required', 'alpha_dash', 'max:50', Rule::unique('tenants', 'slug')],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return self::attributesDefinition();
    }

    /**
     * Provide the reusable attribute labels for validation messages.
     */
    public static function attributesDefinition(): array
    {
        return [
            'company_name' => 'company name',
            'company_slug' => 'company slug',
            'contact_email' => 'contact email',
            'admin_name' => 'administrator name',
            'admin_email' => 'administrator email',
            'admin_password' => 'administrator password',
        ];
    }
}

