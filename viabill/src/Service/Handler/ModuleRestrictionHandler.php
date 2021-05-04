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

namespace ViaBill\Service\Handler;

use ViaBill\Service\Api\Countries\CountryService;
use ViaBill\Service\Api\Locale\LocaleService;
use Language;

/**
 * Class ModuleRestrictionHandler
 *
 * @package ViaBill\Service\Handler
 */
class ModuleRestrictionHandler
{
    /**
     * Locale Services Variable Declaration.
     *
     * @var LocaleService
     */
    private $localeService;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Country Service Variable Declaration.
     *
     * @var CountryService
     */
    private $countryService;

    /**
     * ModuleRestrictionHandler constructor.
     *
     * @param \ViaBill $module
     * @param LocaleService $localeService
     * @param CountryService $countryService
     */
    public function __construct(
        \ViaBill $module,
        LocaleService $localeService,
        CountryService $countryService
    ) {
        $this->localeService = $localeService;
        $this->module = $module;
        $this->countryService = $countryService;
    }

    /**
     * Saves Currency Restriction.
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function saveCurrencyRestriction()
    {
        $locales = $this->localeService->getLocale();

        if (empty($locales)) {
            return false;
        }

        foreach ($locales as $locale) {
            $idCurrency = \Currency::getIdByIsoCode($locale->getCurrencyCode());
            $currency = new \Currency($idCurrency);

            if (\Validate::isLoadedObject($currency)) {
                $added = \Db::getInstance()->insert(
                    'module_currency',
                    array(
                        'id_module' => (int) $this->module->id,
                        'id_currency' => $currency->id
                    ),
                    false,
                    true,
                    \Db::ON_DUPLICATE_KEY
                );
            }
        }

        return true;
    }

    /**
     * Saves Country Restriction.
     *
     * @param Language $language
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function saveCountryRestriction(Language $language)
    {
        $countries = $this->countryService->getCountries($language->iso_code);

        if (empty($countries)) {
            return false;
        }

        $idCountries = array();
        foreach ($countries as $country) {
            $idCountry = \Country::getByIso($country->getCode());
            $country = new \Country($idCountry);
            if (\Validate::isLoadedObject($country)) {
                $idCountries[] = array(
                    'id_country' => $country->id
                );
            }
        }

        return \Country::addModuleRestrictions(
            array(),
            $idCountries,
            array(
                array(
                    'id_module' => $this->module->id
                )
            )
        );
    }
}
