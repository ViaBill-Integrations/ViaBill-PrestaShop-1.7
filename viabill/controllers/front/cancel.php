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

        // Debug info
        $debug_str = '[Cart id: '.$this->id_cart.'][Order id: '.$this->id_order.'][Secure key: '.$this->secure_key.']';
        $order_str = (empty($order))?'empty':var_export($order, true);
        $debug_str .= "[order: {$order_str}]";
        DebugLog::msg("Cancel init / ".$debug_str);

        if (!$this->id_order || !$this->module->id || !$this->secure_key || empty($this->secure_key)) {
            Tools::redirect($redirectLink.(Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }

        if ((string) $this->secure_key !== (string) $order->secure_key ||
            (int) $order->id_customer !== (int) $this->context->customer->id ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($redirectLink);
        }

        if ($order->module !== $this->module->name) {
            Tools::redirect($redirectLink);
        }
        $this->order_presenter = new \PrestaShop\PrestaShop\Adapter\Order\OrderPresenter();
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
        $this->context->smarty->assign(array(
            'order' => $this->order_presenter->present($order)
        ));

        $this->setTemplate(
            sprintf('module:%s/views/templates/front/cancel.tpl', $this->module->name)
        );
    }
}
