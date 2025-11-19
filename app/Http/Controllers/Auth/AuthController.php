<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\Main\Unit;
use App\Models\Main\Company;

class AuthController extends Controller
{
    /**
     * عرض شاشة الدخول
     */
    public function showLoginForm()
    {
        $units = Unit::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();
        
        return view('auth.login', compact('units', 'companies'));
    }

    /**
     * معالجة تسجيل الدخول
     */
    public function login(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'unit_id.required' => 'الوحدة مطلوبة',
            'unit_id.exists' => 'الوحدة المختارة غير موجودة',
            'company_id.exists' => 'المؤسسة المختارة غير موجودة',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        // التحقق من عدد المحاولات (Rate Limiting)
        $this->ensureIsNotRateLimited($request);

        // محاولة تسجيل الدخول
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // حفظ معلومات الوحدة والمؤسسة في الجلسة
            $request->session()->put('unit_id', $request->unit_id);
            $request->session()->put('company_id', $request->company_id);
            
            // مسح محاولات الدخول الفاشلة
            RateLimiter::clear($this->throttleKey($request));

            return redirect()->intended('/');
        }

        // زيادة عداد المحاولات الفاشلة
        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => 'بيانات الدخول غير صحيحة',
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * التحقق من عدم تجاوز الحد الأقصى للمحاولات
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * الحصول على مفتاح Rate Limiting
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')).'|'.$request->ip();
    }
}
