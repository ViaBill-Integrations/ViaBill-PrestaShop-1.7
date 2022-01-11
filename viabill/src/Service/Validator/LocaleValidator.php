<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

namespace ViaBill\Service\Validator;

use Language;
use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use ViaBill\Service\Api\Locale\LocaleService;

/**
 * Class LocaleValidator
 */
class LocaleValidator
{
    /**
     * Locale Services Variable Declaration.
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
     * Checks If Locale Matches.
     *
     * @param Language $language
     *
     * @return bool
     */
    public function isLocaleMatches(Language $language)
    {
        $locales = $this->localeService->getLocale();
        $found = false;
        $currentIso = $this->tools->strToUpper($language->iso_code);

        foreach ($locales as $locale) {
            $localeIso = $this->tools->strToUpper($locale->getLanguage());

            if ($localeIso === $currentIso) {
                $found = true;
                break;
            } elseif (in_array($currentIso, Config::getNorwayIsoExceptionsArray())) {
                if (in_array($localeIso, Config::getNorwayIsoExceptionsArray())) {
                    $found = true;
                    break;
                }
            }
        }

        return $found;
    }
}
