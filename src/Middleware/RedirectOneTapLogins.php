<?php

namespace Javaabu\EfaasSocialite\Middleware;

use Closure;
use Javaabu\EfaasSocialite\EfaasProvider;

abstract class RedirectOneTapLogins
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
        $guards = empty($guards) ? [null] : $guards;

        if ($login_code = $request->query(EfaasProvider::ONE_TAP_LOGIN_KEY)) {
            return redirect()->to($this->addLoginCodeToUrl(
                $this->getRedirectUrl($request),
                $login_code
            ));

        }

        return $next($request);
    }

    /**
     * Add the login code to the redirect request
     */
    protected function addLoginCodeToUrl($url, $login_code)
    {
        // check if the url already contains any query parameters
        $url_parts = parse_url($url);

        $url .= empty($url_parts['query']) ? '?' : '&';

        return $url .= EfaasProvider::ONE_TAP_LOGIN_KEY . '=' . $login_code;
    }

    /**
     * Get the efaas login redirect url
     */
    protected abstract function getRedirectUrl($request);
}
