<?php

namespace Webkul\Marketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Webkul\Core\Repositories\LocaleRepository;

class Locale
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected LocaleRepository $localeRepository)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $localeCode = request()->get('locale');

        if ($localeCode) {
            Session::put('seller_locale', $localeCode);
        }

        if (!$localeCode) {
            $localeCode = Session::get('seller_locale') ?: Session::get('locale');
        }

        $seller = Auth::guard('seller')->user();

        if (
            !$localeCode
            && $seller
            && $seller->locale
        ) {
            $localeCode = $seller->locale;
        }

        if (
            $localeCode
            && $this->localeRepository->findOneByField('code', $localeCode)
        ) {
            app()->setLocale($localeCode);
        } else {
            app()->setLocale(core()->getDefaultLocaleCodeFromDefaultChannel());
        }

        return $next($request);
    }
}

