<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
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
        session_start();
        if (!$_SESSION) {
            return redirect('../');
        }
        session([
            'rapidx_user_id' => $_SESSION["rapidx_user_id"],
            'rapidx_name' => $_SESSION["rapidx_name"],
            'rapidx_username' => $_SESSION["rapidx_username"],
            'rapidx_user_level_id' => $_SESSION["rapidx_user_level_id"],
            'rapidx_email' => $_SESSION["rapidx_email"],
            'rapidx_department_id' => $_SESSION["rapidx_department_id"],
            'rapidx_employee_number' => $_SESSION["rapidx_employee_number"],
        ]);
        return $next($request);

    }
}
