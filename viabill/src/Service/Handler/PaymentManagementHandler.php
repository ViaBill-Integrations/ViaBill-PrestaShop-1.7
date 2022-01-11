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

namespace ViaBill\Service\Handler;

use Context;
use Order;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Handler\HandlerResponse;

/**
 * Class PaymentManagementHandler
 */
class PaymentManagementHandler
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'PaymentManagementHandler';

    /**
     * Cancel Payment Variable Declaration.
     *
     * @var CancelPaymentHandler
     */
    private $cancelPaymentHandler;

    /**
     * Capture Payment Variable Declaration.
     *
     * @var CapturePaymentHandler
     */
    private $capturePaymentHandler;

    /**
     * Refund Payment Variable Declaration.
     *
     * @var RefundPaymentHandler
     */
    private $refundPaymentHandler;

    /**
     * Renew Payment Variable Declaration.
     *
     * @var RenewPaymentHandler
     */
    private $renewPaymentHandler;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Logger Factory Variable Declaration.
     *
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * PaymentManagementHandler constructor.
     *
     * @param \ViaBill $module
     * @param LoggerFactory $loggerFactory
     * @param CancelPaymentHandler $cancelPaymentHandler
     * @param CapturePaymentHandler $capturePaymentHandler
     * @param RefundPaymentHandler $refundPaymentHandler
     * @param RenewPaymentHandler $renewPaymentHandler
     */
    public function __construct(
        \ViaBill $module,
        LoggerFactory $loggerFactory,
        CancelPaymentHandler $cancelPaymentHandler,
        CapturePaymentHandler $capturePaymentHandler,
        RefundPaymentHandler $refundPaymentHandler,
        RenewPaymentHandler $renewPaymentHandler
    ) {
        $this->cancelPaymentHandler = $cancelPaymentHandler;
        $this->capturePaymentHandler = $capturePaymentHandler;
        $this->refundPaymentHandler = $refundPaymentHandler;
        $this->renewPaymentHandler = $renewPaymentHandler;
        $this->module = $module;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Handles Payment Management.
     *
     * @param Order $order
     * @param bool $isCancel
     * @param bool $isCapture
     * @param bool $isRefund
     * @param bool $isRenew
     * @param float $amount
     *
     * @return HandlerResponse
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(
        Order $order,
        $isCancel,
        $isCapture,
        $isRefund,
        $isRenew,
        $amount
    ) {
        $existingOrder = \ViaBillOrder::getPrimaryKey($order->id);
        $isOrderExists = $order->payment === $this->module->name && $existingOrder;

        if (!$isOrderExists) {
            $errorMessage = sprintf(
                $this->module->l('Order %s is not related with viaBill system.', self::FILENAME),
                $order->reference
            );

            return new HandlerResponse(
                $order,
                404,
                [$errorMessage]
            );
        }

        if ($isCancel) {
            return $this->cancelPaymentHandler->handle($order);
        }

        if ($isCapture) {
            return $this->capturePaymentHandler->handle($order, $amount);
        }

        if ($isRefund) {
            return $this->refundPaymentHandler->handle($order, $amount);
        }

        if ($isRenew) {
            return $this->renewPaymentHandler->handle($order);
        }

        return new HandlerResponse($order, 200);
    }

    /**
     * Handles Multiple Payment Management.
     *
     * @param Context $context
     * @param bool $isCancel
     * @param bool $isRefund
     * @param bool $isCapture
     * @param int[]|bool $orderIds
     */
    public function handleMultiple(
        Context $context,
        $isCancel,
        $isRefund,
        $isCapture,
        $orderIds
    ) {
        if (empty($orderIds)) {
            $context->controller->warnings[] =
            sprintf(
                $this->module->l('At least one %s order have to be selected in order to proceed.', self::FILENAME),
                $this->module->displayName
            );
        }

        $orders = $this->getOrders($orderIds);
        $logger = $this->loggerFactory->create();
        $allErrors = [];
        $allWarnings = [];
        $operationName = $this->getOperationName($isCancel, $isRefund, $isCapture);

        foreach ($orders as $order) {
            $amount = $order->total_paid_tax_incl;

            if ($isRefund) {
                $idCapture = \ViaBillOrderCapture::getPrimaryKey($order->id);
                $idRefund = \ViaBillOrderRefund::getPrimaryKey($order->id);

                $capture = new \ViaBillOrderCapture($idCapture);
                $refund = new \ViaBillOrderRefund($idRefund);

                $totalCaptured = $capture->getTotalCaptured();
                $totalRefunded = $refund->getTotalRefunded();

                $refundRemaining = $totalCaptured - $totalRefunded;

                $amount = $refundRemaining;
            }

            $handlerResult = $this->handle($order, $isCancel, $isCapture, $isRefund, false, $amount);
            $errors = $handlerResult->getErrors();
            $warnings = $handlerResult->getWarnings();

            if (!empty($errors)) {
                $errorMessage = sprintf(
                    $this->module->l('%s operation failed for order %s', self::FILENAME),
                    $operationName,
                    $order->reference
                );

                $allErrors[] = $errorMessage;
                $logger->error(
                    $errorMessage,
                    [
                        'errors' => $errors,
                    ]
                );
            }

            if (!empty($warnings)) {
                $warningMessage = sprintf(
                    $this->module->l('%s operation has warnings for order %s', self::FILENAME),
                    $operationName,
                    $order->reference
                );
                $allWarnings[] = $warningMessage;
                $logger->warning(
                    $warningMessage,
                    [
                        'warning' => $warnings,
                    ]
                );
            }
        }

        if (empty($allWarnings) && empty($allErrors)) {
            $context->controller->confirmations[] =
                sprintf($this->module->l('%s operation is completed', self::FILENAME), $operationName);

            return;
        }

        $context->controller->errors = $allErrors;
        $context->controller->warnings = $allWarnings;
    }

    /**
     * Gets Order Objects Array.
     *
     * @param array $orderIds
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getOrders(array $orderIds)
    {
        $result = [];
        foreach ($orderIds as $id) {
            $order = new Order($id);

            if (!\Validate::isLoadedObject($order)) {
                continue;
            }

            $result[] = $order;
        }

        return $result;
    }

    /**
     * Gets Order Operation Name.
     *
     * @param bool $isCancel
     * @param bool $isRefund
     * @param bool $isCapture
     *
     * @return string
     */
    private function getOperationName($isCancel, $isRefund, $isCapture)
    {
        if ($isCancel) {
            return $this->module->l('Cancel', self::FILENAME);
        }

        if ($isRefund) {
            return $this->module->l('Refund', self::FILENAME);
        }

        if ($isCapture) {
            return $this->module->l('Capture', self::FILENAME);
        }

        return '';
    }
}
