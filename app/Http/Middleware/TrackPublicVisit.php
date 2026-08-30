<?php

namespace App\Http\Middleware;

use App\Models\GrantOpportunity;
use App\Services\Analytics\RecordEngagement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPublicVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $opportunity = $request->route('grantOpportunity');
            app(RecordEngagement::class)->record($request, $response, $opportunity instanceof GrantOpportunity ? $opportunity : null, 'view');
        }

        return $response;
    }
}
