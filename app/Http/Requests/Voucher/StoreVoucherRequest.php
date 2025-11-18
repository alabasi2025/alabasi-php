<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\VoucherType;
use App\Enums\PaymentMethod;

class StoreVoucherRequest extends FormRequest
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
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'type' => ['required', Rule::in(array_column(VoucherType::cases(), 'value'))],
            'voucher_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'account_id' => ['required', 'exists:accounts,id'],
            'cash_account_id' => ['required_if:payment_method,cash', 'nullable', 'exists:accounts,id'],
            'bank_account_id' => ['required_if:payment_method,bank,check,transfer', 'nullable', 'exists:accounts,id'],
            'description' => ['required', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'check_number' => ['required_if:payment_method,check', 'nullable', 'string', 'max:50'],
            'check_date' => ['required_if:payment_method,check', 'nullable', 'date'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'المؤسسة',
            'type' => 'نوع السند',
            'voucher_date' => 'تاريخ السند',
            'amount' => 'المبلغ',
            'payment_method' => 'طريقة الدفع',
            'account_id' => 'الحساب',
            'cash_account_id' => 'حساب الصندوق',
            'bank_account_id' => 'حساب البنك',
            'description' => 'الوصف',
            'reference_number' => 'رقم المرجع',
            'check_number' => 'رقم الشيك',
            'check_date' => 'تاريخ الشيك',
            'beneficiary_name' => 'اسم المستفيد',
            'notes' => 'ملاحظات',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'يجب اختيار المؤسسة',
            'type.required' => 'يجب اختيار نوع السند',
            'voucher_date.required' => 'يجب إدخال تاريخ السند',
            'amount.required' => 'يجب إدخال المبلغ',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'payment_method.required' => 'يجب اختيار طريقة الدفع',
            'account_id.required' => 'يجب اختيار الحساب',
            'cash_account_id.required_if' => 'يجب اختيار حساب الصندوق عند اختيار الدفع النقدي',
            'bank_account_id.required_if' => 'يجب اختيار حساب البنك عند اختيار الدفع البنكي',
            'description.required' => 'يجب إدخال وصف السند',
            'check_number.required_if' => 'يجب إدخال رقم الشيك',
            'check_date.required_if' => 'يجب إدخال تاريخ الشيك',
        ];
    }
}
