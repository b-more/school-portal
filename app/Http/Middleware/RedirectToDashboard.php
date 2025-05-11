<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Constants\RoleConstants;

class RedirectToDashboard
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
        // Only redirect if user is on the base admin URL and is authenticated
        if ($request->is('admin') && auth()->check()) {
            $user = auth()->user();

            // Redirect based on user role to their specific dashboard
            switch ($user->role_id) {
                case RoleConstants::TEACHER:
                    return redirect()->route('filament.admin.pages.teacher-dashboard');
                case RoleConstants::PARENT:
                    return redirect()->route('filament.admin.pages.parent-dashboard');
                case RoleConstants::STUDENT:
                    return redirect()->route('filament.admin.pages.student-dashboard');
                case RoleConstants::ADMIN:
                default:
                    return redirect()->route('filament.admin.pages.dashboard');
            }
        }

        return $next($request);
    }
}
