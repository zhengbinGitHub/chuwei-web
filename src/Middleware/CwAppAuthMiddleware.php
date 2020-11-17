<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 18:06
 */

namespace CwApp\Middleware;

use Closure;

class CwAppAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = '')
    {
        $userInfo = $request->user($guard);
        if(!isset($userInfo->id)) {
            return redirect('/logout');
        }
        $request->tenant_id = $userInfo->id;
        return $next($request);
    }
}