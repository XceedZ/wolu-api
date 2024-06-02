<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateLastActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'is_online' => true,
                    'last_activity' => Carbon::now(),
                ]);
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            DB::table('users')
                ->where('id', $userId)
                ->update(['is_online' => false]);
        }
    }
}
