<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

namespace ViaBill\Service\Validator\Payment;

use Currency;
use ViaBill\Adapter\Tools;
use ViaBill\Service\Api\Locale\LocaleService;

/**
 * Class CurrencyValidator
 *
 * @package ViaBill\Service\Validator\Payment
 */
class CurrencyValidator
{
    /**
     * Locale Service Variable Declaration.
     *
     * @var LocaleService
     */
    private $localeService;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * CurrencyValidator constructor.
     *
     * @param LocaleService $localeService
     * @param Tools $tools
     */
    public function __construct(LocaleService $localeService, Tools $tools)
    {
        $this->localeService = $localeService;
        $this->tools = $tools;
    }

    /**
     * Checks If Currency Matches.
     *
     * @param Currency $currency
     *
     * @return bool
     */
    public function isCurrencyMatches(Currency $currency)
    {
        $locales = $this->localeService->getLocale();
        $found = false;
        $currentCurrency = $this->tools->strToUpper($currency->iso_code);

        foreach ($locales as $locale) {
            $localeCurrency = $this->tools->strToUpper($locale->getCurrencyCode());

            if ($localeCurrency === $currentCurrency) {
                $found = true;
                break;
            }
        }

        return $found;
    }
}
