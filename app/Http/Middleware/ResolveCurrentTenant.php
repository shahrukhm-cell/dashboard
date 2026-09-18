<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenant = null;
        $availableTenants = collect();

        if ($request->user()) {
            $availableTenants = $request->user()
                ->tenants()
                ->orderBy('name')
                ->get();

            $tenantId = $request->session()->get('current_tenant_id');

            $currentTenant = $availableTenants->firstWhere('id', $tenantId)
                ?? $availableTenants->first();

            if ($currentTenant) {
                $request->session()->put('current_tenant_id', $currentTenant->id);
            } else {
                $request->session()->forget('current_tenant_id');
            }
        }

        app()->instance('currentTenant', $currentTenant);

        View::share([
            'availableTenants' => $availableTenants,
            'currentTenant' => $currentTenant,
        ]);

        return $next($request);
    }
}
