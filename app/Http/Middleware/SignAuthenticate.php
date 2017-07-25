<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\Helper\Token;
use App\Helper\Protocol;

class SignAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!in_array(strtolower($request->method()), ['put', 'post']) || is_dev()) {
            return $next($request);
        }

        $sign = Protocol::verifySign();

        if ($sign === false) {
            return show_error(10003, trans('message.sign.invalid'));
        }

        if ($sign === 'sign_expired') {
            return show_error(10004, trans('message.sign.expired'));
        }

        if ($sign === 'request_encrypt_error') {
            return show_error(400, trans('message.error.request_encrypt'));
        }

        return $next($request);
    }

}
