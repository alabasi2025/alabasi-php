<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContextSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // استثناء صفحة اختيار السياق نفسها
        if ($request->is('context/*')) {
            return $next($request);
        }

        // التحقق من وجود وحدة ومؤسسة محددة
        if (!session('active_unit_id') || !session('active_company_id')) {
            return redirect()->route('context.selector')
                ->with('info', 'يرجى اختيار الوحدة والمؤسسة للمتابعة');
        }

        return $next($request);
    }
}
