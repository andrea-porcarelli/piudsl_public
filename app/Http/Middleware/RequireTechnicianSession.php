<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTechnicianSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $request->session()->has('auth_token') ||
            $request->session()->get('user_role') !== 'technician'
        ) {
            return redirect('/');
        }

        return $next($request);
    }
}
