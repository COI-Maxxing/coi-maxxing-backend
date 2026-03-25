<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetRLSContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check())
        {
            // Set the session variable at SESSION level (false), not transaction level (true)
            // This ensures RLS policies can see it across all queries
            DB::statement("
                SELECT set_config('app.current_company_id', ?, false)
            ", [Auth::user()->company_id]);
        }

        return $next($request);
    }
}
