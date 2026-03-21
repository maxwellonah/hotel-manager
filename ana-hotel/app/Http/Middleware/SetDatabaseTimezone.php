<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class SetDatabaseTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Set database timezone to match system timezone (Pacific Time)
        DB::statement("SET time_zone = '-07:00';");
        
        return $next($request);
    }
}
