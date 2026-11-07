<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Webkul\RestApi\Exceptions\Handler as RestApiHandler;

/**
 * The exception handler the application actually uses.
 *
 * The Core package handler extends App\Exceptions\Handler and the REST API
 * package's handler extends the core one, so this class sits at the end of
 * that chain. Both packages bind their own handler while registering, which
 * is why AppServiceProvider binds this one after every provider has registered.
 *
 * It keeps the REST API JSON error shapes and makes sure the themed shop and
 * admin error pages are used, with the right status code.
 */
class ThemedHandler extends RestApiHandler
{
    /**
     * Status codes that have their own themed error page. Anything else is shown as a 500.
     *
     * @var array<int>
     */
    protected array $pageStatusCodes = [401, 403, 404, 503];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        /**
         * Http errors such as a 404 are not debugging events, so they get the
         * themed page even while debugging is enabled.
         */
        $this->handleHttpException();

        if (config('app.debug')) {
            return;
        }

        $this->handleAuthenticationException();

        $this->handleValidationException();

        $this->handleServerException();
    }

    /**
     * Handle the http exceptions.
     */
    protected function handleHttpException(): void
    {
        $this->renderable(function (HttpException $exception, Request $request) {
            $namespace = $this->namespace($request);

            $errorCode = in_array($exception->getStatusCode(), $this->pageStatusCodes)
                ? $exception->getStatusCode()
                : 500;

            if ($request->wantsJson()) {
                return response()->json([
                    'title'   => trans("rest-api::app.{$namespace}.errors.{$errorCode}.title"),
                    'message' => trans("rest-api::app.{$namespace}.errors.{$errorCode}.message"),
                ], $errorCode);
            }

            return $this->errorPage($namespace, $errorCode);
        });
    }

    /**
     * Handle the server exceptions.
     */
    protected function handleServerException(): void
    {
        $this->renderable(function (Throwable $throwable, Request $request) {
            $namespace = $this->namespace($request);

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => trans("rest-api::app.{$namespace}.errors.message"),
                ], 500);
            }

            return $this->errorPage($namespace, 500);
        });
    }

    /**
     * Whether the request belongs to the admin panel or the storefront.
     */
    protected function namespace(Request $request): string
    {
        return $request->is(config('app.admin_url').'/*') ? 'admin' : 'shop';
    }

    /**
     * Render the themed error page with the matching http status code.
     *
     * The storefront layout needs an active theme, which the shop's theme
     * middleware normally sets. An error raised outside that middleware, such
     * as an unmatched non-GET url or a failure in an earlier middleware, would
     * otherwise blow up while rendering the error page itself.
     *
     * @return \Illuminate\Http\Response
     */
    protected function errorPage(string $namespace, int $errorCode)
    {
        if ($namespace === 'shop' && ! themes()->current()) {
            $channelTheme = core()->getCurrentChannel()?->theme;

            themes()->set(
                $channelTheme && themes()->exists($channelTheme)
                    ? $channelTheme
                    : config('themes.shop-default')
            );
        }

        return response()->view("{$namespace}::errors.index", compact('errorCode'), $errorCode);
    }
}
