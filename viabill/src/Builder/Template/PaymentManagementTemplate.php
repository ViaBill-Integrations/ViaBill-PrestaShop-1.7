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

namespace ViaBill\Builder\Template;

use Currency;
use ViaBill\Adapter\Configuration;
use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use ViaBill\Service\Provider\OrderStatusProvider;
use Language;
use Order;

/**
 * Class PaymentManagementTemplate
 *
 * @package ViaBill\Builder\Payment
 */
class PaymentManagementTemplate implements TemplateInterface
{

    /**
     * Filename Constant.
     */
    const FILENAME = 'PaymentManagementTemplate';

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Form Action Variable Declaration.
     *
     * @var string
     */
    private $formAction;

    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Order Status Provider Variable Declaration.
     *
     * @var OrderStatusProvider
     */
    private $statusProvider;

    /**
     * Order Variable Declaration.
     *
     * @var Order
     */
    private $order;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Language Variable Declaration.
     *
     * @var Language
     */
    private $language;

    /**
     * PaymentManagementTemplate constructor.
     *
     * @param \ViaBill $module
     * @param Configuration $configuration
     * @param OrderStatusProvider $statusProvider
     * @param Tools $tools
     */
    public function __construct(
        \ViaBill $module,
        Configuration $configuration,
        OrderStatusProvider $statusProvider,
        Tools $tools
    ) {
        $this->module = $module;
        $this->configuration = $configuration;
        $this->statusProvider = $statusProvider;
        $this->tools = $tools;
    }

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
     * Sets Order From Given Param.
     *
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Sets Language From Given Param.
     *
     * @param Language $language
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Sets Form Action From Given Param.
     *
     * @param string $formAction
     */
    public function setFormAction($formAction)
    {
        $this->formAction = $formAction;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        $isCancelled = $this->statusProvider->isCancelled($this->order);
        $remainingToRefund = $this->statusProvider->getRemainingToRefund($this->order);
        $currency = new \Currency($this->order->id_currency, $this->order->id_lang);

        return array(
            'paymentManagement' => array(
                'formAction' => $this->formAction,
                'orderId' => $this->order->id,
                'currencyError' => $this->checkCurrency(),
                'isCancelled' => $isCancelled,
                'isFullRefund' => $this->isFullRefund(),
                'currencySign' => $currency->getSign(),
                'cancelFormGroup' => array(
                    'isVisible' => $this->isCancelVisible(),
                    'cancelConfirmation' => $this->configuration->get(Config::SINGLE_ACTION_CANCEL_CONF_MESSAGE)
                ),
                'captureFormGroup' => array(
                    'isVisible' => $this->isCaptureVisible(),
                    'captureConfirmation' => $this->configuration->get(Config::SINGLE_ACTION_CAPTURE_CONF_MESSAGE),
                    'remainingToCapture' => $this->getRemainingToCapture($this->order->total_paid_tax_incl)
                ),
                'refundFormGroup' => array(
                    'isVisible' => $this->isRefundVisible(),
                    'remainingToRefund' => $remainingToRefund,
                    'refundConfirmation' => $this->configuration->get(Config::SINGLE_ACTION_REFUND_CONF_MESSAGE)
                ),
                'renewFormGroup' => array(
                    'isVisible' => $this->isRenewVisible()
                )
            )
        );
    }

    /**
     * Gets Smarty Payment Management HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());
        return $this->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/payment-management.tpl'
        );
    }

    /**
     * Gets Remaining Price To Capture.
     *
     * @param float $orderTotal
     *
     * @return float
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getRemainingToCapture($orderTotal)
    {
        $idOrderCapture = \ViaBillOrderCapture::getPrimaryKey($this->order->id);
        $orderCapture = new \ViaBillOrderCapture($idOrderCapture);

        return (float) $orderTotal - (float) $orderCapture->getTotalCaptured();
    }

    /**
     * Checks If Cancel Action Is Visible.
     *
     * @return bool
     */
    private function isCancelVisible()
    {
        return $this->statusProvider->canBeCancelled($this->order);
    }

    /**
     * Checks If Refund Action Is Visible.
     *
     * @return bool
     */
    private function isRefundVisible()
    {
        return $this->statusProvider->canBeRefunded($this->order);
    }

    /**
     * Checks If Renew Action Is Visible.
     *
     * @return bool
     */
    private function isRenewVisible()
    {
        return $this->statusProvider->canBeRenewed($this->order);
    }

    /**
     * Checks If Capture Action Is Visible.
     *
     * @return bool
     */
    private function isCaptureVisible()
    {
        return $this->statusProvider->canBeCaptured($this->order);
    }

    /**
     * Checks If Performed Refund Is Full Refund.
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function isFullRefund()
    {
        $idOrderCapture = \ViaBillOrderCapture::getPrimaryKey($this->order->id);
        $orderCapture = new \ViaBillOrderCapture($idOrderCapture);
        $idRefund = \ViaBillOrderRefund::getPrimaryKey($this->order->id);
        $refund = new \ViaBillOrderRefund($idRefund);

        if (!\Validate::isLoadedObject($orderCapture) || !\Validate::isLoadedObject($refund)) {
            return false;
        }

        $totalCaptured = $this->tools->displayNumber($orderCapture->getTotalCaptured());
        $totalRefunded = $this->tools->displayNumber($refund->getTotalRefunded());

        return $totalCaptured === $totalRefunded;
    }

    /**
     * Checks Currency.
     *
     * @return string
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function checkCurrency()
    {
        $primary = \ViaBillOrder::getPrimaryKey($this->order->id);
        $orderMark = new \ViaBillOrder($primary);
        $message = '';

        if ((int) $this->order->id_currency !== (int) $orderMark->id_currency) {
            $currency = new Currency($orderMark->id_currency, $this->language->id);

            $message =
                sprintf(
                    $this->module->l('For current order, only %s is supported currency.', self::FILENAME),
                    $currency->name
                );
        }
        return $message;
    }
}
