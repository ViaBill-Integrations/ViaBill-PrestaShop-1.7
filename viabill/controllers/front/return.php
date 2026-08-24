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
 * ViaBill Checkout Module Front Controller Class.
 *
 * Class ViaBillReturnModuleFrontController
 */
class ViaBillReturnModuleFrontController extends ModuleFrontController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    public function postProcess()
    {
        $orderId = (int) Tools::getValue('id_order');
        $order = new Order($orderId);

        /*
         * PATCH: validate the order before doing anything with it.
         *
         * Previously this controller accepted any id_order from the query
         * string with no ownership check, and then redirected the visitor to
         * the order-confirmation page using the order's real secure_key
         * fetched server-side. That both leaked confirmation pages for
         * arbitrary order ids and allowed a cancelled payment to end on the
         * "your order is confirmed" page.
         */
        if (!$orderId ||
            !Validate::isLoadedObject($order) ||
            $order->module !== $this->module->name ||
            (int) $order->id_customer !== (int) $this->context->customer->id
        ) {
            DebugLog::msg(
                'Return postProcess / invalid or foreign order. ' .
                '[order id: ' . $orderId . ']' .
                '[context customer: ' . (int) $this->context->customer->id . ']'
            );

            Tools::redirect('index.php?controller=history');
        }

        /**
         * @var \ViaBill\Util\LinksGenerator $linkGenerator
         */
        $linkGenerator = $this->module->getModuleContainer()->get('util.linkGenerator');

        /**
         * @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatusProvider
         */
        $orderStatusProvider = $this->module->getModuleContainer()->get('service.provider.orderStatus');

        /*
         * PATCH: the status API call can throw. Treat an unreachable /
         * erroring status API as "unknown", never as success.
         */
        $isOrderApproved = false;
        $isStatusKnown = true;
        $isOrderCancelled = false;

        try {
            $isOrderApproved = $orderStatusProvider->isApproved($order);

            if (!$isOrderApproved) {
                $isOrderCancelled = $orderStatusProvider->isCancelled($order);
            }
        } catch (Exception $statusException) {
            $isStatusKnown = false;

            DebugLog::msg(
                'Return postProcess / status API check failed: ' .
                $statusException->getMessage()
            );
        }

        if ($isOrderApproved) {
            /**
             * @var \ViaBill\Service\Cart\MemorizeCartService $memorizeService
             */
            $memorizeService = $this->module->getModuleContainer()->get('cart.memorizeCartService');
            $memorizeService->removeMemorizedCart($order);
        }

        // update transaction history
        if ($orderId) {
            $idTransactionHistory = \ViaBillTransactionHistory::getPrimaryKeyByOrder($orderId);
            if ($idTransactionHistory) {
                $transactionHistory = new \ViaBillTransactionHistory($idTransactionHistory);
                $transactionHistory->updateAfterComplete($isOrderApproved);
            }
        }

        // Debug info
        $debug_str = '[Order id: ' . $orderId . ']';
        $approved_str = ($isOrderApproved) ? '[approved]' : '[not approved]';
        $approved_str .= ($isStatusKnown) ? '' : '[status unknown]';
        $approved_str .= ($isOrderCancelled) ? '[cancelled at viabill]' : '';
        $debug_str .= $approved_str;
        DebugLog::msg('Return processPost / ' . $debug_str);

        /*
         * PATCH: only approved payments reach the order-confirmation page.
         */
        if ($isOrderApproved) {
            Tools::redirect($linkGenerator->getOrderConfirmationLink(
                $this->context->link,
                $order
            ));
        }

        /*
         * Payment is not approved.
         *
         * If ViaBill explicitly reports the transaction as CANCELLED and the
         * order is still pending on the shop side, mark it cancelled now
         * (mirrors the CANCELLED callback, which may be blocked or delayed),
         * then send the customer to the module's cancel page.
         *
         * If the status could not be determined (API failure), do not touch
         * the order state - the callback remains the source of truth - and
         * send the customer to their order history with an error message.
         */
        if ($isStatusKnown && $isOrderCancelled) {
            $this->cancelPendingOrder($order);

            Tools::redirect($linkGenerator->getCancelLink(
                $this->context->link,
                $order
            ));
        }

        $this->errors[] = $this->module->l(
            'Your ViaBill payment has not been approved yet. If you have completed the payment, the order status will be updated shortly.',
            'return'
        );

        $this->redirectWithNotifications('index.php?controller=history');
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

            if (!$pendingStateId || !$canceledStateId || $currentStateId !== $pendingStateId) {
                return;
            }

            $order->setCurrentState($canceledStateId);

            DebugLog::msg(
                'Return cancelPendingOrder / order #' . (int) $order->id .
                ' moved to "Payment canceled by ViaBill" (state ' . $canceledStateId . ').'
            );
        } catch (Exception $exception) {
            DebugLog::msg('Return cancelPendingOrder / exception: ' . $exception->getMessage());
        }
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:viabill/views/templates/front/return.tpl');
    }
}
