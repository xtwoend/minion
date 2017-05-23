<?php

namespace Minion\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $permission=null)
    {
        if (Auth::guest()) {
            return redirect('/login');
        }

        if (! $request->user()->hasRole($role) && ! $request->user()->isAdmin()) {
           abort(403);
           // throw new AuthenticationException('Unpermission.');
        }
        
        if (! is_null($permission) && ! $request->user()->can($permission) && ! $request->user()->isAdmin()) {
           abort(403);
           // throw new AuthenticationException('Unpermission.');
        }

        return $next($request);
    }
}
