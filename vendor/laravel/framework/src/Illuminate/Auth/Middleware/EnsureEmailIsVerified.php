<?php

namespace Illuminate\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        logger("## vendorフォルダの内部を修正した処理が動いています");
        logger("## vendor\laravel\framework\src\Illuminate\Auth\Middleware\EnsureEmailIsVerified#handle");
        $intendedUrl = url()->full();
        session(['url.intended' => $intendedUrl]);
        logger("## session中の'url.intended'の値を${intendedUrl}に設定しました。");
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            return $request->expectsJson()
                    ? abort(403, 'Your email address is not verified.')
                    : Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
