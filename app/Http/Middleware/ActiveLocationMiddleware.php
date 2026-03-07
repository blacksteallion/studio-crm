<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveLocationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !session()->has('active_location_id')) {
            
            // If Super Admin, default to Global View
            if (Auth::user()->hasRole('Super Admin')) {
                session(['active_location_id' => 'all']);
            } else {
                // Otherwise, default to the staff member's first assigned location
                $defaultLocation = Auth::user()->locations()->first();
                if ($defaultLocation) {
                    session(['active_location_id' => $defaultLocation->id]);
                }
            }
        }

        return $next($request);
    }
}