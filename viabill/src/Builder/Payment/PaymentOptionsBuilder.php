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
use ViaBill\Service\LegalNote\LegalNoteService;
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
     * Legal Note Service Variable Declaration.
     *
     * @var LegalNoteService
     */
    private $legalNoteService;

    /**
     * PaymentOptionsBuilder constructor.
     *
     * @param \ViaBill $module
     * @param TagBodyTemplate $tagBodyTemplate
     * @param CurrencyValidator $currencyValidator
     * @param LegalNoteService $legalNoteService
     */
    public function __construct(
        \ViaBill $module,
        TagBodyTemplate $tagBodyTemplate,
        CurrencyValidator $currencyValidator,
        LegalNoteService $legalNoteService
    ) {
        $this->module = $module;
        $this->tagBodyTemplate = $tagBodyTemplate;
        $this->currencyValidator = $currencyValidator;
        $this->legalNoteService = $legalNoteService;
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

        $paymentOptions = [];

        if (!Config::isHideInCheckout()) {

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

            $this->setAdditionalInformation($paymentOption, 'monthly');

            $paymentOptions[] = $paymentOption;
        }

        // Try now, buy later Viabill payment method
        if (Config::isTBYBAvailable(null, $this->currency)) {
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
                    $tryPaymentOption->setCallToActionText($this->module->l('Pay with ViaBill', self::FILENAME));
                }

                $tryPaymentOption->setModuleName($this->module->name);

                $this->setAdditionalInformation($tryPaymentOption, 'tbyb');

                $paymentOptions[] = $tryPaymentOption;                                
            }
        }
        
        return $paymentOptions;
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
     * Sets The Legal Note And The Price Tag (If Active) As The Payment
     * Option's Additional Information.
     *
     * @param PaymentOption $paymentOption
     * @param string $payment_method 'monthly' or 'tbyb'
     *
     * @throws \SmartyException
     */
    private function setAdditionalInformation(PaymentOption $paymentOption, $payment_method)
    {
        $html = $this->getLegalNoteHtml();

        if ($this->module->isPriceTagActive($this->controller)) {
            $html .= $this->constructTag($payment_method);
        }

        if ($html !== '') {
            $paymentOption->setAdditionalInformation($html);
        }
    }

    /**
     * Gets The Legal Note HTML, Or Empty String If It Is Disabled.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    private function getLegalNoteHtml()
    {
        $legalNoteText = $this->legalNoteService->getText($this->language);
        if ($legalNoteText === '') {
            return '';
        }

        $this->smarty->assign(['legalNoteText' => $legalNoteText]);

        return $this->smarty->fetch($this->module->getLocalPath() . 'views/templates/front/legal-note.tpl');
    }

    /**
     * Constructs Price Tag.
     *
     * @param string $payment_method
     *
     * @return string Price tag HTML.
     *
     * @throws \SmartyException
     */
    private function constructTag($payment_method = null)
    {
        $this->tagBodyTemplate->setSmarty($this->smarty);
        $this->tagBodyTemplate->setPrice($this->orderPrice);
        $this->tagBodyTemplate->setView(Config::DATA_PAYMENT);
        $this->tagBodyTemplate->setLanguage($this->language);
        $this->tagBodyTemplate->setCurrency($this->currency);
        $this->tagBodyTemplate->setProductTypes($payment_method);
        $this->smarty->assign($this->tagBodyTemplate->getSmartyParams());
        return $this->tagBodyTemplate->getHtml();
    }
}
