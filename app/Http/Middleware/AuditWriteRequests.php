<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditWriteRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $response->getStatusCode() < 400) {
            ActivityLog::record('request.'.$request->method(), null, $request, 'Write request completed.', [
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
                'input_keys' => collect($request->except(['password', 'password_confirmation', 'current_password', '_token']))->keys()->values()->all(),
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}
