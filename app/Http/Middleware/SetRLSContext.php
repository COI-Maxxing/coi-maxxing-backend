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
            DB::beginTransaction();

            DB::statement("
                SELECT set_config('app.current_company_id', ?, true )
            ", [Auth::user()->company_id]);
        }

        return $next($request);
    }
}
