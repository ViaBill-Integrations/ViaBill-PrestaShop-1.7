<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Controller;

use ViaBill\Service\Order\OrderListActionsService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

class OrderListActionController extends FrameworkBundleAdminController
{
    /**
     * @var OrderListActionsService
     */
    private $orderListActionService;

    /**
     * OrderListActionController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $module = \Module::getInstanceByName('viabill');
        $this->orderListActionService = $module->getModuleContainer()->get('service.order.orderListActions');
    }

    /**
     * Capture payment viabill
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @param int $orderId
     * @return Response
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function capturePayment(int $orderId)
    {
        $this->orderListActionService->getOrderListActionResult(
            $this->getContext(),
            $orderId,
            false,
            true,
            false,
            false
        );
        $this->setFlashMessages();
        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }

    /**
     * Cancel payment viabill
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @param int $orderId
     * @return Response
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function cancelPayment(int $orderId)
    {
        $this->orderListActionService->getOrderListActionResult(
            $this->getContext(),
            $orderId,
            true,
            false,
            false,
            false
        );
        $this->setFlashMessages();
        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }

    /**
     * Refund payment viabill
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @param int $orderId
     * @return Response
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function refundPayment(int $orderId)
    {
        $this->orderListActionService->getOrderListActionResult(
            $this->getContext(),
            $orderId,
            false,
            false,
            true,
            false
        );
        $this->setFlashMessages();
        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bulkCapturePayments()
    {
        $orderIds = \Tools::getValue('order_orders_bulk');
        $this->orderListActionService->getOrderListMultipleActionResult(
            $orderIds,
            $this->getContext(),
            false,
            false,
            true
        );
        $this->setFlashMessages();

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bulkCancelPayments()
    {
        $orderIds = \Tools::getValue('order_orders_bulk');
        $this->orderListActionService->getOrderListMultipleActionResult(
            $orderIds,
            $this->getContext(),
            true,
            false,
            false
        );
        $this->setFlashMessages();

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bulkRefundPayments()
    {
        $orderIds = \Tools::getValue('order_orders_bulk');
        $this->orderListActionService->getOrderListMultipleActionResult(
            $orderIds,
            $this->getContext(),
            false,
            true,
            false
        );
        $this->setFlashMessages();

        return $this->redirectToRoute('admin_orders_index');
    }

    private function setFlashMessages()
    {
        if ($this->getContext()->controller->errors) {
            foreach ($this->getContext()->controller->errors as $error) {
                $this->addFlash('error', $error);
            }
        }

        if ($this->getContext()->controller->warnings) {
            foreach ($this->getContext()->controller->warnings as $warning) {
                $this->addFlash('warning', $warning);
            }
        }

        if ($this->getContext()->controller->confirmations) {
            foreach ($this->getContext()->controller->confirmations as $confirmation) {
                $this->addFlash('success', $confirmation);
            }
        }
    }
}
