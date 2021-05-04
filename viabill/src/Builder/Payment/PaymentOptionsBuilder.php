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

namespace ViaBill\Builder\Payment;

use Configuration;
use ViaBill\Builder\Template\TagBodyTemplate;
use ViaBill\Config\Config;
use ViaBill\Service\Validator\Payment\CurrencyValidator;
use Link;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class PaymentOptionsBuilder
 *
 * @package ViaBill\Builder\Payment
 */
class PaymentOptionsBuilder
{

    /**
     * Filename Constant.
     */
    const FILENAME = 'PaymentOptionsBuilder';

    /**
     * Link Variable Declaration.
     *
     * @var Link
     */
    private $link;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Tag Body Template Variable Declaration.
     *
     * @var TagBodyTemplate
     */
    private $tagBodyTemplate;

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Order Price Variable Declaration.
     *
     * @var float
     */
    private $orderPrice;

    /**
     * Controller Variable Declaration.
     *
     * @var string
     */
    private $controller;

    /**
     * Language Variable Declaration.
     *
     * @var \Language
     */
    private $language;

    /**
     * Currency Variable Declaration.
     *
     * @var \Currency
     */
    private $currency;

    /**
     * Currency Validator Variable Declaration.
     *
     * @var CurrencyValidator
     */
    private $currencyValidator;

    /**
     * PaymentOptionsBuilder constructor.
     *
     * @param \ViaBill $module
     * @param TagBodyTemplate $tagBodyTemplate
     * @param CurrencyValidator $currencyValidator
     */
    public function __construct(
        \ViaBill $module,
        TagBodyTemplate $tagBodyTemplate,
        CurrencyValidator $currencyValidator
    ) {
        $this->module = $module;
        $this->tagBodyTemplate = $tagBodyTemplate;
        $this->currencyValidator = $currencyValidator;
    }

    /**
     * Sets Link From Given Param.
     *
     * @param Link $link
     */
    public function setLink(\Link $link)
    {
        $this->link = $link;
    }

    /**
     * Sets Smarty From Given Param.
     *
     * @param \Smarty $smarty
     */
    public function setSmarty($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Sets Order Price From Given Param.
     *
     * @param float $orderPrice
     */
    public function setOrderPrice($orderPrice)
    {
        $this->orderPrice = $orderPrice;
    }

    /**
     * Sets Controller From Given Param.
     *
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Sets Language From Given Param.
     *
     * @param \Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Sets Currency From Given Param.
     *
     * @param \Currency $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Gets ViaBill Payment Option.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getPaymentOptions()
    {
        if (!$this->currencyValidator->isCurrencyMatches($this->currency)) {
            return array();
        }

        $url = $this->link->getModuleLink($this->module->name, 'checkout');

        $paymentOption = new PaymentOption();
        $paymentOption->setAction($url);
        $paymentOption->setCallToActionText($this->module->l('Pay with ViaBill', self::FILENAME));

        if (Configuration::get(Config::VIABILL_LOGO_DISPLAY_IN_CHECKOUT)) {
            $paymentOption->setLogo($this->module->getPathUri().'views/img/viabill.png');
        }

        $paymentOption->setModuleName($this->module->name);

        if ($this->module->isPriceTagActive($this->controller)) {
            $this->constructTag($paymentOption);
        }

        return array($paymentOption);
    }

    /**
     * Constructs Price Tag.
     *
     * @param PaymentOption $paymentOption
     *
     * @throws \SmartyException
     */
    private function constructTag(PaymentOption $paymentOption)
    {
        $this->tagBodyTemplate->setSmarty($this->smarty);
        $this->tagBodyTemplate->setPrice($this->orderPrice);
        $this->tagBodyTemplate->setView(Config::DATA_PAYMENT);
        $this->tagBodyTemplate->setLanguage($this->language);
        $this->tagBodyTemplate->setCurrency($this->currency);
        $this->smarty->assign($this->tagBodyTemplate->getSmartyParams());
        $html = $this->tagBodyTemplate->getHtml();
        $paymentOption->setAdditionalInformation($html);
    }
}
