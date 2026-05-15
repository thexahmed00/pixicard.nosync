<?php

namespace Webkul\Pos\Queries\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Core\Repositories\LocaleRepository;
use Webkul\Pos\Repositories\PosBankRepository;

class GlobalDataQuery extends Controller
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected CoreConfigRepository $coreConfigRepository,
        protected LocaleRepository $localeRepository,
        protected CurrencyRepository $currencyRepository,
        protected PosBankRepository $posBankRepository
    ) {}

    /**
     * Login page details.
     */
    public function index(): array
    {
        $locales = $this->localeRepository->all();

        $currencies = $this->currencyRepository->all();

        $configurations = $this->coreConfigRepository
            ->where('code', 'LIKE', 'pos.settings.%')
            ->get()
            ->map(function ($config) {
                if ($config->code == 'pos.settings.general.pos_logo') {
                    $config->value = Storage::url($config->value);
                }

                return $config;
            })
            ->toArray();

        $channel = core()->getDefaultChannel();

        return [
            'locales'        => $locales,
            'currencies'     => $currencies,
            'configurations' => $configurations,
            'countries'      => core()->countries(),
            'country_states' => $this->groupedStatesByCountries(),
            'base_currency'  => $channel->base_currency->code,
            'default_locale' => $channel->default_locale->code,
        ];
    }

    /**
     * Get the countries with their states.
     */
    private function groupedStatesByCountries(): array
    {
        $countryStates = [];

        foreach (core()->groupedStatesByCountries() as $countryCode => $states) {
            $countryStates[] = [
                'countryCode' => $countryCode,
                'states'      => $states,
            ];
        }

        return $countryStates;
    }
}
