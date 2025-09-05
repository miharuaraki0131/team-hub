<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // もし、ログインしていて、かつ、is_adminフラグがtrueなら…
        if (Auth::check() && Auth::user()->is_admin) {
            // 何もせず、そのまま通す
            return $next($request);
        }

        // 条件を満たさない場合は、403 Forbiddenエラーで弾く
        abort(403, '管理者権限がありません。');
    }
}
