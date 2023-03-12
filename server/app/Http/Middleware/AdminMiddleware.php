<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\ApiController as ApiController;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware extends ApiController
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
        if (!$request->user() || !$request->user()->tokenCan('admin')) {
            return $this->sendError('Validation Error.');
        }

        return $next($request);
    }
}
