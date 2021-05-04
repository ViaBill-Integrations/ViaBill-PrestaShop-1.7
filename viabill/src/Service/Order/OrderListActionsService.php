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

namespace ViaBill\Service\Order;

use ViaBill\Service\Handler\PaymentManagementHandler;
use ViaBill\Service\MessageService;
use ViaBill\Service\Provider\OrderStatusProvider;
use ViaBill\Util\NumberUtility;
use Order;

class OrderListActionsService
{
    /**
     * @var PaymentManagementHandler
     */
    private $paymentHandler;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @var OrderStatusProvider
     */
    private $orderStatusProvider;

    public function __construct(
        PaymentManagementHandler $paymentHandler,
        MessageService $messageService,
        OrderStatusProvider $orderStatusProvider
    ) {
        $this->paymentHandler = $paymentHandler;
        $this->messageService = $messageService;
        $this->orderStatusProvider = $orderStatusProvider;
    }

    /**
     * @param $context
     * @param $orderId
     * @param bool $isCancel
     * @param bool $isCapture
     * @param bool $isRefund
     * @param bool $isRenew
     * @return false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderListActionResult(
        $context,
        $orderId,
        $isCancel = false,
        $isCapture = false,
        $isRefund = false,
        $isRenew = false
    ) {
        $order = new Order($orderId);
        $amount = 0.0;

        if (!\Validate::isLoadedObject($order)) {
            return false;
        }

        if ($isCapture) {
            $amount = $this->getCaptureAmount($order);
        }

        if ($isRefund) {
            $amount = $this->getRefundAmount($order);
        }

        $handleResponse = $this->paymentHandler->handle(
            $order,
            $isCancel,
            $isCapture,
            $isRefund,
            $isRenew,
            $amount
        );

        $errors = $handleResponse->getErrors();
        $warnings = $handleResponse->getWarnings();
        $confirmations = array();

        if (empty($errors) && $handleResponse->getSuccessMessage()) {
            $confirmations[] = $handleResponse->getSuccessMessage();
        }
        $context->controller->errors = $errors;
        $context->controller->warnings = $warnings;
        $context->controller->confirmations = $confirmations;
    }

    /**
     * @param $orderIds
     * @param null $context
     * @param bool $isCancel
     * @param bool $isRefund
     * @param bool $isCapture
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderListMultipleActionResult(
        $orderIds,
        $context = null,
        $isCancel = false,
        $isRefund = false,
        $isCapture = false
    ) {
        $this->paymentHandler->handleMultiple(
            $context,
            $isCancel,
            $isRefund,
            $isCapture,
            $orderIds
        );
    }

    /**
     * @param $order
     * @return float
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getCaptureAmount(Order $order)
    {
        $amount = $order->total_paid_tax_incl;

        if ($this->orderStatusProvider->canBeCaptured($order)) {
            $amount = $this->orderStatusProvider->getRemainingToCapture($order);
        }

        return (float) NumberUtility::replaceCommaToDot($amount);
    }

    /**
     * @param $order
     * @return float
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getRefundAmount(Order $order)
    {
        return  (float) NumberUtility::replaceCommaToDot($this->orderStatusProvider->getRemainingToRefund($order));
    }
}
