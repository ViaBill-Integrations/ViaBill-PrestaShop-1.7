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

use ViaBill\Config\Config;
use ViaBill\Util\DebugLog;

/**
 * ViaBill Cancel Module Front Controller Class.
 *
 * Class ViaBillCancelModuleFrontController
 */
class ViaBillCancelModuleFrontController extends ModuleFrontController
{
    /**
     * ID Order Variable Declaration.
     *
     * @var
     */
    private $id_order;

    /**
     * Security Key Variable Declaration.
     *
     * @var
     */
    private $secure_key;

    /**
     * ID Cart Variable Declaration.
     *
     * @var
     */
    private $id_cart;

    /**
     * Order Presenter Variable Declaration.
     *
     * @var
     */
    private $order_presenter;

    /**
     * Performing ViaBill Payment Cancellation And Redirects.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        $this->id_cart = (int) Tools::getValue('id_cart', 0);

        $redirectLink = 'index.php?controller=history';

        $this->id_order = Order::getIdByCartId((int) ($this->id_cart));
        $this->secure_key = Tools::getValue('key', false);
        $order = new Order((int) $this->id_order);

        // update transaction history
        $idOrder = $this->id_order;
        if ($idOrder) {
            $transactionHistory = new \ViaBillTransactionHistory();
            $idTransactionHistory = \ViaBillTransactionHistory::getPrimaryKeyByOrder($idOrder);
            if ($idTransactionHistory) {
                $transactionHistory = new \ViaBillTransactionHistory($idTransactionHistory);
                $cancelResponse = array(
                    'cart' => $this->id_cart,
                    'order_id' => $this->id_order,
                    'secure_key' => $this->secure_key
                );
                $transactionHistory->updateAfterCancel($cancelResponse);
            }
        }

        // Debug info
        $debug_str = '[Cart id: ' . $this->id_cart . '][Order id: ' . $this->id_order . '][Secure key: ' . $this->secure_key . ']';
        $order_str = (empty($order)) ? 'empty' : var_export($order, true);
        $debug_str .= "[order: {$order_str}]";
        DebugLog::msg('Cancel init / ' . $debug_str);

        if (!$this->id_order || !$this->module->id || !$this->secure_key || empty($this->secure_key)) {
            Tools::redirect($redirectLink . (Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }

        if (!Validate::isLoadedObject($order) ||
            (string) $this->secure_key !== (string) $order->secure_key ||
            (int) $order->id_customer !== (int) $this->context->customer->id
        ) {
            Tools::redirect($redirectLink);
        }

        if ($order->module !== $this->module->name) {
            Tools::redirect($redirectLink);
        }

        /*
         * PATCH: actually mark the order as cancelled.
         *
         * Previously this controller only updated the transaction history and
         * displayed the cancel template, leaving the order in the
         * "Payment pending by ViaBill" state forever if ViaBill did not send
         * (or the shop did not receive) the CANCELLED server-to-server
         * callback. Any later merchant workflow could then treat a
         * never-paid order as a live one.
         *
         * We only transition orders that are still in the ViaBill pending
         * state, and as a safety net we ask the ViaBill status API first:
         * if the transaction turns out to be APPROVED (e.g. the customer
         * paid and then hit the cancel URL manually, or callbacks raced),
         * we leave the order alone and let the callback/return flow handle it.
         */
        $this->cancelPendingOrder($order);

        $this->order_presenter = new \PrestaShop\PrestaShop\Adapter\Order\OrderPresenter();
    }

    /**
     * Sets the order state to "Payment canceled by ViaBill" when it is safe to do so.
     *
     * @param Order $order
     */
    private function cancelPendingOrder(Order $order)
    {
        try {
            $pendingStateId = (int) Configuration::get(Config::PAYMENT_PENDING);
            $canceledStateId = (int) Configuration::get(Config::PAYMENT_CANCELED);
            $currentStateId = (int) $order->getCurrentState();

            // Only ever transition orders that are still awaiting ViaBill approval.
            if (!$pendingStateId || !$canceledStateId || $currentStateId !== $pendingStateId) {
                DebugLog::msg(
                    'Cancel cancelPendingOrder / skipped. ' .
                    '[current state: ' . $currentStateId . ']' .
                    '[pending state: ' . $pendingStateId . ']' .
                    '[canceled state: ' . $canceledStateId . ']'
                );

                return;
            }

            // Best-effort verification against the ViaBill status API. If the
            // transaction was actually approved, do NOT cancel the order.
            $isApproved = false;
            try {
                /**
                 * @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatusProvider
                 */
                $orderStatusProvider = $this->module->getModuleContainer()
                    ->get('service.provider.orderStatus');
                $isApproved = $orderStatusProvider->isApproved($order);
            } catch (Exception $statusException) {
                // The status API being unreachable must not block a
                // customer-initiated (secure-key validated) cancellation of a
                // pending order. Log and proceed.
                DebugLog::msg(
                    'Cancel cancelPendingOrder / status API check failed: ' .
                    $statusException->getMessage()
                );
            }

            if ($isApproved) {
                DebugLog::msg(
                    'Cancel cancelPendingOrder / order #' . (int) $order->id .
                    ' is APPROVED at ViaBill, refusing to cancel.'
                );

                return;
            }

            $order->setCurrentState($canceledStateId);

            DebugLog::msg(
                'Cancel cancelPendingOrder / order #' . (int) $order->id .
                ' moved to "Payment canceled by ViaBill" (state ' . $canceledStateId . ').'
            );
        } catch (Exception $exception) {
            // Never break the cancel page rendering because of this.
            DebugLog::msg('Cancel cancelPendingOrder / exception: ' . $exception->getMessage());
        }
    }

    /**
     * Adding ViaBill Payment Cancel Template To Checkout Order Confirmation.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $order = new Order($this->id_order);
        $this->context->smarty->assign([
            'order' => $this->order_presenter->present($order),
        ]);

        $this->setTemplate(
            sprintf('module:%s/views/templates/front/cancel.tpl', $this->module->name)
        );
    }
}
