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

use Cart;
use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use Link;

/**
 * Class PaymentValidator
 * @package ViaBill\Service\Validator\Payment
 */
class PaymentValidator
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Config Variable Declaration.
     *
     * @var Config
     */
    private $config;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Cart Validation Variable Declaration.
     *
     * @var CartValidator
     */
    private $cartValidation;

    /**
     * Order Validator Variable Declaration.
     *
     * @var OrderValidator
     */
    private $orderValidator;

    /**
     * Currency Validator Variable Declaration.
     *
     * @var CurrencyValidator
     */
    private $currencyValidator;

    /**
     * PaymentValidator constructor.
     *
     * @param \ViaBill $module
     * @param Config $config
     * @param Tools $tools
     * @param CartValidator $cartValidation
     * @param OrderValidator $orderValidator
     * @param CurrencyValidator $currencyValidator
     */
    public function __construct(
        \ViaBill $module,
        Config $config,
        Tools $tools,
        CartValidator $cartValidation,
        OrderValidator $orderValidator,
        CurrencyValidator $currencyValidator
    ) {
        $this->module = $module;
        $this->config = $config;
        $this->tools = $tools;
        $this->cartValidation = $cartValidation;
        $this->orderValidator = $orderValidator;
        $this->currencyValidator = $currencyValidator;
    }

    /**
     * Validates Payment.
     *
     * @param Link $link
     * @param Cart $cart
     * @param \Customer $customer
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function validate(Link $link, Cart $cart, \Customer $customer)
    {
        $isPaymentProcess = $this->tools->isSubmit('id_order');
        $redirectLink = $this->getRedirectLink($link, $isPaymentProcess);

        if (!$this->isModuleConfigured()) {
            $this->tools->redirect($redirectLink);
        }

        if ($isPaymentProcess) {
            $idOrder = $this->tools->getValue('id_order');
            $order = new \Order($idOrder);

            $validationResult = $this->orderValidator->validate($order, $customer);
        } else {
            $validationResult = $this->cartValidation->validate($cart, $customer);
        }

        $currency = new \Currency($cart->id_currency);

        if (!$validationResult->isValidationAccepted() || !$this->currencyValidator->isCurrencyMatches($currency)) {
            $this->tools->redirect($redirectLink);
        }

        return true;
    }

    /**
     * Gets Payment Redirect Link.
     *
     * @param Link $link
     * @param bool $isPaymentProcess
     *
     * @return string
     */
    private function getRedirectLink(Link $link, $isPaymentProcess)
    {
        if ($isPaymentProcess) {
            return $link->getPageLink('history');
        }

        return $link->getPageLink('order');
    }

    /**
     * Checks If Module Is Configured.
     *
     * @return bool
     */
    private function isModuleConfigured()
    {
        return $this->module->active && $this->config->isLoggedIn();
    }
}
