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

namespace ViaBill\Builder\Template;

use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;

/**
 * Class TagBodyTemplate
 */
class TagBodyTemplate implements TemplateInterface
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Language Variable Declaration.
     *
     * @var \Language
     */
    private $language;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Currency Variable Declaration.
     *
     * @var \Currency
     */
    private $currency;

    /**
     * Dynamic Price Selector Variable Declaration.
     *
     * @var string
     */
    private $dynamicPriceSelector;

    /**
     * Dynamic Price Trigger Variable Declaration.
     *
     * @var string
     */
    private $dynamicPriceTrigger;

    /**
     * TagScriptTemplate constructor.
     *
     * @param \ViaBill $module
     * @param Tools $tools
     */
    public function __construct(\ViaBill $module, Tools $tools)
    {
        $this->module = $module;
        $this->tools = $tools;
    }

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Data View Variable Declaration.
     *
     * @var string - enumeration in the config file
     */
    private $dataView;

    /**
     * Data Price Variable Declaration.
     *
     * @var float
     */
    private $dataPrice;

    /**
     * Use Columns Variable Declaration.
     *
     * @var bool
     */
    private $useColumns;

    /**
     * Use Extra Gap Variable Declaration.
     *
     * @var bool
     */
    private $useExtraGap;

    /**
     * Sets Smarty From Given Param.
     *
     * @param \Smarty $smarty
     */
    public function setSmarty(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Sets Language From Given Param.
     *
     * @param \Language $language
     */
    public function setLanguage(\Language $language)
    {
        $this->language = $language;
    }

    /**
     * Sets Currency From Given Param.
     *
     * @param \Currency $currency
     */
    public function setCurrency(\Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Sets View From Given Param.
     *
     * @param string $dataView
     */
    public function setView($dataView)
    {
        $this->dataView = $dataView;
    }

    /**
     * Sets Price From Given Param.
     *
     * @param float $dataPrice
     */
    public function setPrice($dataPrice)
    {
        $this->dataPrice = $dataPrice;
    }

    /**
     * Sets Dynamic Price Selector From Given Param.
     *
     * @param string $dynamicPriceSelector
     */
    public function setDynamicPriceSelector($dynamicPriceSelector)
    {
        $this->dynamicPriceSelector = $dynamicPriceSelector;
    }

    /**
     * Sets Dynamic Price Trigger From Given Param.
     *
     * @param string $dynamicPriceTrigger
     */
    public function setDynamicPriceTrigger($dynamicPriceTrigger)
    {
        $this->dynamicPriceTrigger = $dynamicPriceTrigger;
    }

    /**
     * Checks If Columns Needs To Be Used.
     *
     * @param bool $useColumns
     */
    public function useColumns($useColumns)
    {
        $this->useColumns = $useColumns;
    }

    /**
     * Checks If Extra Gap Need To Be Used.
     *
     * @param bool $useExtraGap
     */
    public function useExtraGap($useExtraGap)
    {
        $this->useExtraGap = $useExtraGap;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return [
            'dataView' => $this->dataView,
            'dataPrice' => $this->dataPrice,
            'dataLanguageIso' => $this->tools->strToUpper($this->language->iso_code),
            'dataCurrencyIso' => $this->tools->strToUpper($this->currency->iso_code),
            'dataCountryCodeIso' => Config::getCountryISOCodeByCurrencyISO($this->currency->iso_code),
            'dynamicPriceSelector' => $this->dynamicPriceSelector,
            'dynamicPriceTrigger' => $this->dynamicPriceTrigger,
            'useColumns' => $this->useColumns,
            'useExtraGap' => $this->useExtraGap,
        ];
    }

    /**
     * Gets Smarty Tag Body HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        return $this->smarty->fetch($this->module->getLocalPath() . 'views/templates/front/tag-body.tpl');
    }
}
