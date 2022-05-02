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

use ViaBill\Util\DebugLog;
use ViaBillTransactionHistory;

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
        $orderId = Tools::getValue('id_order');
        $order = new Order($orderId);

        /**
         * @var \ViaBill\Util\LinksGenerator $linkGenerator
         */
        $linkGenerator = $this->module->getModuleContainer()->get('util.linkGenerator');

        /**
         * @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatusProvider
         */
        $orderStatusProvider = $this->module->getModuleContainer()->get('service.provider.orderStatus');

        $isOrderApproved = $orderStatusProvider->isApproved($order);
        if ($isOrderApproved) {
            /**
             * @var \ViaBill\Service\Cart\MemorizeCartService $memorizeService
             */
            $memorizeService = $this->module->getModuleContainer()->get('cart.memorizeCartService');
            $memorizeService->removeMemorizedCart($order);
        }
        
        // update transaction history       
        if ($orderId) {
            $idTransactionHistory = ViaBillTransactionHistory::getPrimaryKeyByOrder($orderId);
            if ($idTransactionHistory) {
                $transactionHistory = new ViaBillTransactionHistory($idTransactionHistory);
                $transactionHistory->updateAfterComplete($isOrderApproved);
            }
        }

        // Debug info
        $debug_str = '[Order id: ' . $orderId . ']';
        $approved_str = ($isOrderApproved) ? '[approved]' : '[not approved]';
        $debug_str .= $approved_str;
        $order_str = (empty($order)) ? 'empty' : var_export($order, true);
        $debug_str .= "[order: {$order_str}]";
        DebugLog::msg('Return processPost / ' . $debug_str);

        Tools::redirect($linkGenerator->getOrderConfirmationLink(
            $this->context->link,
            $order
        ));
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:viabill/views/templates/front/return.tpl');
    }
}
