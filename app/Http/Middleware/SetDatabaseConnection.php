<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Main\Unit;
use Illuminate\Support\Facades\Config;

class SetDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // الحصول على معرف الوحدة من الـ session أو الـ request
        $unitId = $request->session()->get('current_unit_id') ?? $request->input('unit_id');

        if ($unitId) {
            // الحصول على معلومات الوحدة من القاعدة المركزية
            $unit = Unit::find($unitId);

            if ($unit && $unit->is_active) {
                // حفظ معرف الوحدة في الـ session
                $request->session()->put('current_unit_id', $unit->id);
                $request->session()->put('current_unit_name', $unit->name);
                $request->session()->put('current_unit_connection', $unit->database_name);

                // تعيين الـ connection الافتراضي للـ Unit Models
                Config::set('database.default_unit_connection', $unit->database_name);
            }
        }

        // الحصول على معرف المؤسسة من الـ session أو الـ request
        $companyId = $request->session()->get('current_company_id') ?? $request->input('company_id');

        if ($companyId) {
            $request->session()->put('current_company_id', $companyId);
        }

        return $next($request);
    }
}
