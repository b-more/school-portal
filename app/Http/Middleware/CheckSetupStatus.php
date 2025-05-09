<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemConfiguration;
use Symfony\Component\HttpFoundation\Response;

class CheckSetupStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for these routes
        $allowedRoutes = [
            'filament.admin.pages.setup-wizard',
            'filament.admin.auth.login',
            'filament.admin.auth.logout',
        ];

        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // Check if setup is completed
        $setupCompleted = SystemConfiguration::where('key', 'system.setup_completed')
            ->where('value', 'true')
            ->exists();

        if (!$setupCompleted) {
            return redirect()->route('filament.admin.pages.setup-wizard');
        }

        return $next($request);
    }
}
