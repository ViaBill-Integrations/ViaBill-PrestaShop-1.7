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

namespace ViaBill\Object\Api\Locale;

/**
 * Class Locale
 */
class Locale
{
    /**
     * Locale ISO Code Variable Declaration.
     *
     * @var string
     */
    private $isoCode;

    /**
     * Locale Language Variable Declaration.
     *
     * @var string
     */
    private $language;

    /**
     * Locale Currency Code Variable Declaration.
     *
     * @var string
     */
    private $currencyCode;

    /**
     * Locale constructor.
     *
     * @param string $isoCode
     * @param string $language
     * @param string $currencyCode
     */
    public function __construct($isoCode, $language, $currencyCode)
    {
        $this->isoCode = $isoCode;
        $this->language = $language;
        $this->currencyCode = $currencyCode;
    }

    /**
     * Gets Locale ISO Code.
     *
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Gets Locale Language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Gets Locale Currency Code.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }
}
