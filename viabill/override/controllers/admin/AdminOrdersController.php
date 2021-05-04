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

/**
 * Override Of PS Admin Orders Controller Class.
 *
 * Class AdminOrdersController
 */
class AdminOrdersController extends AdminOrdersControllerCore
{
    /**
     * AdminOrdersController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initSingleActions();
    }

    /**
     * Checks For Order Actions ANd Handle Them.
     *
     * @return bool|ObjectModel|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $bulkPrefix = 'submitBulk';
        $isCancel = Tools::isSubmit($bulkPrefix.'cancelViaBillPaymentorder');
        $isRefund = Tools::isSubmit($bulkPrefix.'refundViaBillPaymentorder');
        $isCapture = Tools::isSubmit($bulkPrefix.'captureViaBillPaymentorder');
        if (!$isCancel && !$isRefund && !$isCapture) {
            return parent::postProcess();
        }
        $orderIds = Tools::getValue('orderBox');
        /**
         * @var ViaBill $module
         */
        $module = Module::getInstanceByName('viabill');
        /**
         * @var \ViaBill\Service\Handler\PaymentManagementHandler $orderHandler
         */
        $orderHandler = $module->getModuleContainer()->get('service.handler.paymentManagement');
        $orderHandler->handleMultiple(
            $this->context,
            $isCancel,
            $isRefund,
            $isCapture,
            $orderIds
        );

        return parent::postProcess();
    }

    /**
     * Getting Order List.
     *
     * @param int $id_lang
     * @param null $order_by
     * @param null $order_way
     * @param int $start
     * @param null $limit
     * @param bool $id_lang_shop
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        /**
         * @var ViaBill $module
         */
        $module = Module::getInstanceByName('viabill');

        $isCancelMessage = Configuration::get(ViaBill\Config\Config::BULK_ACTION_CANCEL_CONF_MESSAGE);
        $isCaptureMessage = Configuration::get(ViaBill\Config\Config::BULK_ACTION_CAPTURE_CONF_MESSAGE);
        $isRefundMessage = Configuration::get(ViaBill\Config\Config::BULK_ACTION_REFUND_CONF_MESSAGE);

        $orderIds = ViaBillOrder::getOrderIds();
        $osError = (int) Configuration::get(ViaBill\Config\Config::PAYMENT_ERROR);
        $osRefund = (int) Configuration::get(ViaBill\Config\Config::PAYMENT_REFUNDED);
        $osCancelled = (int) Configuration::get(ViaBill\Config\Config::PAYMENT_CANCELED);
        $immutableStates = array($osError, $osRefund, $osCancelled);
        $occurrences = 0;
        if (!empty($this->_list)) {
            foreach ($this->_list as $listItem) {
                $order = new Order($listItem['id_order']);
                if ($listItem['payment'] === 'viabill' &&
                    !in_array((int) $order->current_state, $immutableStates, true) &&
                    in_array($listItem['id_order'], $orderIds, true)
                ) {
                    $occurrences++;
                    if ($occurrences > 1) {
                        $this->appendBulkActions($module, $isCancelMessage, $isCaptureMessage, $isRefundMessage);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Displays Capture Payment Link.
     *
     * @param string $token
     * @param int $idOrder
     *
     * @return string|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayCapturePaymentLink($token, $idOrder)
    {
        /** @var ViaBill $module */
        $module = Module::getInstanceByName('viabill');
        $order = new Order($idOrder);
        if ($this->isNotViaBillOrder($order, $module)) {
            return;
        }

        /** @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatus */
        $orderStatus = $module->getModuleContainer()->get('service.provider.orderStatus');
        if (!$orderStatus->canBeCaptured($order)) {
            return;
        }

        /** @var \ViaBill\Builder\Template\ListButtonTemplate $listButton */
        $listButton = $module->getModuleContainer()->get('builder.template.listButton');
        /** @var \ViaBill\Install\Tab $tab */
        $tab = $module->getModuleContainer()->get('tab');

        $listButton->setSmarty($this->context->smarty);

        $amount = $order->total_paid_tax_incl;

        $actionsLink = $this->context->link->getAdminLink(
            $tab->getControllerActionsName(),
            true,
            array(),
            array(
                'capture_amount' => $amount,
                'id_order' => $order->id
            )
        );

        $actionsLink .= '&capturePayment';

        $listButton->setLink($actionsLink);
        $listButton->setName($module->getSingleActionTranslations('capture'));

        $isCapture = Configuration::get(ViaBill\Config\Config::SINGLE_ACTION_CAPTURE_CONF_MESSAGE);
        $message = $isCapture ? $module->getConfirmationTranslation('capture', $order) : '';
        $listButton->setConfMessage($message);
        return $listButton->getHtml();
    }

    /**
     * Displays Refund Payment Link.
     *
     * @param string $token
     * @param int $idOrder
     *
     * @return string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function displayRefundPaymentLink($token, $idOrder)
    {
        /** @var ViaBill $module */
        $module = Module::getInstanceByName('viabill');
        $order = new Order($idOrder);
        if ($this->isNotViaBillOrder($order, $module)) {
            return;
        }

        /** @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatus */
        $orderStatus = $module->getModuleContainer()->get('service.provider.orderStatus');
        if (!$orderStatus->canBeRefunded($order)) {
            return;
        }

        /** @var \ViaBill\Builder\Template\ListButtonTemplate $listButton */
        $listButton = $module->getModuleContainer()->get('builder.template.listButton');
        /** @var \ViaBill\Install\Tab $tab */
        $tab = $module->getModuleContainer()->get('tab');

        $listButton->setSmarty($this->context->smarty);
        $amount = $orderStatus->getRemainingToRefund($order);

        $actionsLink = $this->context->link->getAdminLink(
            $tab->getControllerActionsName(),
            true,
            array(),
            array(
                'refund_amount' => $amount,
                'id_order' => $order->id
            )
        );

        $actionsLink .= '&refundPayment';

        $listButton->setLink($actionsLink);
        $listButton->setName($module->getSingleActionTranslations('refund'));

        $isRefundConfirmation = Configuration::get(ViaBill\Config\Config::SINGLE_ACTION_REFUND_CONF_MESSAGE);
        $message = $isRefundConfirmation ? $module->getConfirmationTranslation('refund', $order, $amount) : '';
        $listButton->setConfMessage($message);
        return $listButton->getHtml();
    }

    /**
     * Displays Cancel Payment Link.
     *
     * @param string $token
     * @param int $idOrder
     *
     * @return string|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayCancelPaymentLink($token, $idOrder)
    {
        /** @var ViaBill $module */
        $module = Module::getInstanceByName('viabill');
        $order = new Order($idOrder);

        if ($this->isNotViaBillOrder($order, $module)) {
            return;
        }

        /** @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatus */
        $orderStatus = $module->getModuleContainer()->get('service.provider.orderStatus');
        if (!$orderStatus->canBeCancelled($order)) {
            return;
        }

        /** @var \ViaBill\Builder\Template\ListButtonTemplate $listButton */
        $listButton = $module->getModuleContainer()->get('builder.template.listButton');
        /** @var \ViaBill\Install\Tab $tab */
        $tab = $module->getModuleContainer()->get('tab');

        $listButton->setSmarty($this->context->smarty);

        $actionsLink = $this->context->link->getAdminLink(
            $tab->getControllerActionsName(),
            true,
            array(),
            array(
                'id_order' => $order->id
            )
        );

        $actionsLink .= '&cancelPayment';

        $listButton->setLink($actionsLink);
        $listButton->setName($module->getSingleActionTranslations('cancel'));

        $isCancelConfirmation =
            Configuration::get(\ViaBill\Config\Config::SINGLE_ACTION_CANCEL_CONF_MESSAGE);
        $message = $isCancelConfirmation ? $module->getConfirmationTranslation('cancel', $order) : '';
        $listButton->setConfMessage($message);

        return $listButton->getHtml();
    }

    /**
     * Checking If Its Not A ViaBill Order.
     *
     * @param Order $order
     * @param ViaBill $module
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function isNotViaBillOrder(Order $order, ViaBill $module)
    {
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $module->getModuleContainer()->get('config');

        $isMuleOrder = $order->module === $module->name;
        $isLogged = $config->isLoggedIn();

        $idViaBillOrder = ViaBillOrder::getPrimaryKey($order->id);
        $viaBillOrder = new ViaBillOrder($idViaBillOrder);

        $isOrderAccepted = Validate::isLoadedObject($viaBillOrder);

        return !$isMuleOrder || !$isLogged || !$isOrderAccepted;
    }

    /**
     * Appending ViaBill Order Bulk Actions.
     *
     * @param ViaBill $module
     * @param bool $isCancelMessage
     * @param bool $isCaptureMessage
     * @param bool $isRefundMessage
     */
    private function appendBulkActions(ViaBill $module, $isCancelMessage, $isCaptureMessage, $isRefundMessage)
    {
        if (!$this->isOverrideActive()) {
            return;
        }
        $this->bulk_actions['captureViaBillPayment'] = array(
            'text' => $this->l('Capture payments')
        );
        $this->bulk_actions['cancelViaBillPayment'] = array(
            'text' => $this->l('Cancel payments')
        );
        $this->bulk_actions['refundViaBillPayment'] = array(
            'text' => $this->l('Refund payments')
        );
        if ($isCancelMessage) {
            $this->bulk_actions['cancelViaBillPayment']['confirm'] =
                $module->getConfirmationMessageTranslation('cancel');
        }
        if ($isCaptureMessage) {
            $this->bulk_actions['captureViaBillPayment']['confirm'] =
                $module->getConfirmationMessageTranslation('capture');
        }

        if ($isRefundMessage) {
            $this->bulk_actions['refundViaBillPayment']['confirm'] =
                $module->getConfirmationMessageTranslation('refund');
        }
    }

    /**
     * Checks Is Override Active.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function isOverrideActive()
    {
        if (!Module::isEnabled('viabill')) {
            return false;
        }
        /**
         * @var ViaBill $module
         */
        $module = Module::getInstanceByName('viabill');
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $module->getModuleContainer()->get('config');
        if (!$config->isLoggedIn()) {
            return false;
        }
        return true;
    }

    /**
     * Init Single Actions.
     */
    private function initSingleActions()
    {
        $this->addRowAction('capturePayment');
        $this->addRowAction('refundPayment');
        $this->addRowAction('cancelPayment');
    }
}
