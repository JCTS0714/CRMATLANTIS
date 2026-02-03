<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLeadPermissions
{
    /**
     * Handle an incoming request.
     *
     * Validates that the authenticated user has the required permission for lead operations.
     * Used to centralize permission checks that were previously scattered in controllers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission required (e.g., 'leads.create', 'leads.edit')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        // Check if user has the required permission
        if (!$request->user()->can($permission)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        return $next($request);
    }
}
