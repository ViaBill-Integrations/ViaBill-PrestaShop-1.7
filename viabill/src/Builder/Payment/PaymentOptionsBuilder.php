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

namespace ViaBill\Builder\Payment;

use Configuration;
use Link;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use ViaBill\Builder\Template\TagBodyTemplate;
use ViaBill\Config\Config;
use ViaBill\Service\Validator\Payment\CurrencyValidator;

/**
 * Class PaymentOptionsBuilder
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

    /*
    * @var Array
    */
    private $productTypes;

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
    public function setLink(Link $link)
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
     * Sets Product Types based on payment method.
     *
     * @param string $payment_method
     */
    public function setProductTypes($payment_method)
    {        
        if ($payment_method == 'tbyb') {
            $this->productTypes = ['tbyb'];
        } else {
            $this->productTypes = ['light','liberty','plus'];
        }
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
            return [];
        }

        $url = $this->link->getModuleLink($this->module->name, 'checkout');

        // regular Viabill payment method

        $paymentOption = new PaymentOption();
        $paymentOption->setAction($url);

        if (Configuration::get(Config::VIABILL_LOGO_DISPLAY_IN_CHECKOUT)) {
            // Hide payment method name by commenting the following line
            // $paymentOption->setCallToActionText($this->module->l('Pay with ViaBill', self::FILENAME));            
            $lang = strtolower($this->language->iso_code);
            if ($lang) {
                $paymentOption->setLogo($this->module->getPathUri() . 'views/img/viabill_logo_tagline.'.$lang.'.png');
            } else {
                $paymentOption->setLogo($this->module->getPathUri() . 'views/img/viabill_logo_tagline.png');
            }            
        } else {
            $paymentOption->setCallToActionText($this->module->l('Pay with ViaBill', self::FILENAME));
        }

        $paymentOption->setModuleName($this->module->name);

        if ($this->module->isPriceTagActive($this->controller)) {
            $this->constructTag($paymentOption, 'monthly');
        }

        // Try now, buy later Viabill payment method
        if (Config::TRY_BEFORE_YOU_BUY_SHOW_SETTING_OPTION) {
            if (Configuration::get(Config::ENABLE_TRY_BEFORE_YOU_BUY)) {
            
                $url = $this->link->getModuleLink($this->module->name, 'checkout');
                $url = $this->addTryBeforeYouBuyURLParam($url);            
                
                $tryPaymentOption = new PaymentOption();
                $tryPaymentOption->setAction($url);        

                if (Configuration::get(Config::VIABILL_LOGO_DISPLAY_IN_CHECKOUT)) {
                    // Hide payment method name by commenting the following line
                    // $paymentOption->setCallToActionText($this->module->l('Pay with ViaBill', self::FILENAME));            
                    $lang = strtolower($this->language->iso_code);
                    if ($lang) {
                        $tryPaymentOption->setLogo($this->module->getPathUri() . 'views/img/viabill_try_logo_tagline.'.$lang.'.png');
                    } else {
                        $tryPaymentOption->setLogo($this->module->getPathUri() . 'views/img/viabill_try_logo_tagline.png');
                    }            
                } else {
                    $tryPaymentOption->setCallToActionText($this->module->l('Pay with ViaBill - Try before you Buy', self::FILENAME));
                }

                $tryPaymentOption->setModuleName($this->module->name);

                if ($this->module->isPriceTagActive($this->controller)) {
                    $this->constructTag($tryPaymentOption, 'tbyb');
                }
                
                // Return both of payment methods
                return [$paymentOption, $tryPaymentOption];
            } else {
                return [$paymentOption];    
            }
        } else {
            return [$paymentOption];
        }        
    }

    /**
     * Constructs Price Tag.
     *
     * @param string $url
     * 
     * @return string          
     */
    private function addTryBeforeYouBuyURLParam($url) { 
        if (strpos($url, '?')!==false) {
            $url = $url . '&trybeforeyoubuy=1'; 
        } else {
            $url = $url . '?trybeforeyoubuy=1';
        }        
        return $url; 
    }

    /**
     * Constructs Price Tag.
     *
     * @param PaymentOption $paymentOption
     *
     * @throws \SmartyException
     */
    private function constructTag(PaymentOption $paymentOption, $payment_method = null)
    {
        $this->tagBodyTemplate->setSmarty($this->smarty);
        $this->tagBodyTemplate->setPrice($this->orderPrice);
        $this->tagBodyTemplate->setView(Config::DATA_PAYMENT);
        $this->tagBodyTemplate->setLanguage($this->language);
        $this->tagBodyTemplate->setCurrency($this->currency);
        $this->tagBodyTemplate->setProductTypes($payment_method);
        $this->smarty->assign($this->tagBodyTemplate->getSmartyParams());
        $html = $this->tagBodyTemplate->getHtml();
        $paymentOption->setAdditionalInformation($html);
    }
}
