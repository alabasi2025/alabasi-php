<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AccountType;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $accountId = $this->route('account');
        
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where('company_id', $this->company_id ?? $this->account->company_id);
                })->ignore($accountId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_column(AccountType::cases(), 'value'))],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_analytical' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'رمز الحساب',
            'name' => 'اسم الحساب',
            'name_en' => 'الاسم بالإنجليزية',
            'type' => 'نوع الحساب',
            'parent_id' => 'الحساب الأب',
            'description' => 'الوصف',
            'is_active' => 'الحالة',
            'is_analytical' => 'حساب تحليلي',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'يجب إدخال رمز الحساب',
            'code.unique' => 'رمز الحساب مستخدم مسبقاً في هذه المؤسسة',
            'name.required' => 'يجب إدخال اسم الحساب',
            'type.required' => 'يجب اختيار نوع الحساب',
            'type.in' => 'نوع الحساب غير صحيح',
            'parent_id.exists' => 'الحساب الأب المحدد غير موجود',
        ];
    }
}
