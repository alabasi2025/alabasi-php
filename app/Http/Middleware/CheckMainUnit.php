<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMainUnit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // التحقق من أن المستخدم في القاعدة المركزية
        if (!session('is_main')) {
            return redirect('/')->with('error', 'هذه الصفحة متاحة فقط للقاعدة المركزية');
        }

        return $next($request);
    }
}
