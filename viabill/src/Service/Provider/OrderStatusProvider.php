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

namespace ViaBill\Service\Provider;

use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use ViaBill\Object\Api\Status\StatusRequest;
use ViaBill\Service\Api\Status\StatusService;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;
use Order;
use ViaBillOrderCapture;

/**
 * Class OrderStatusProvider
 *
 * @package ViaBill\Service\Provider
 */
class OrderStatusProvider
{
    /**
     * Status Services Variable Declaration.
     *
     * @var StatusService
     */
    private $statusService;

    /**
     * Signatures Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * OrderStatusProvider constructor.
     *
     * @param StatusService $statusService
     * @param SignaturesGenerator $signaturesGenerator
     * @param UserService $userService
     * @param Tools $tools
     */
    public function __construct(
        StatusService $statusService,
        SignaturesGenerator $signaturesGenerator,
        UserService $userService,
        Tools $tools
    ) {
        $this->statusService = $statusService;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->userService = $userService;
        $this->tools = $tools;
    }

    /**
     * Checks If Order Status Is Cancelled.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isCancelled(Order $order)
    {
        $status = $this->getStatus($order);

        return Config::ORDER_STATUS_CANCELLED === $status->getStatus();
    }

    /**
     * Checks If Order Status Is Approved.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isApproved(Order $order)
    {
        $status = $this->getStatus($order);

        return Config::ORDER_STATUS_APPROVED === $status->getStatus();
    }

    /**
     * Checks If Order Can Be Cancelled.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function canBeCancelled(Order $order)
    {
        $primary = ViaBillOrderCapture::getPrimaryKey($order->id);
        $orderCapture = new ViaBillOrderCapture($primary);
        $ordersCaptured = $orderCapture->getCapturedOrdersCount();
        $hasCapturedOrders = $ordersCaptured !== 0;

        if ($hasCapturedOrders) {
            return false;
        }

        $primaryRefund =\ViaBillOrderRefund::getPrimaryKey($order->id);
        $orderRefund = new \ViaBillOrderRefund($primaryRefund);
        $ordersRefunded = $orderRefund->getRefundedOrdersCount();
        $hasRefundOrders = $ordersRefunded !== 0;

        if ($hasRefundOrders) {
            return false;
        }

        return true;
    }

    /**
     * Checks If Order Can Be Captured.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function canBeCaptured(Order $order)
    {
        $refundPrimary = \ViaBillOrderRefund::getPrimaryKey($order->id);
        $orderRefund = new \ViaBillOrderRefund($refundPrimary);

        if (\Validate::isLoadedObject($orderRefund)) {
            return false;
        }

        $primary = ViaBillOrderCapture::getPrimaryKey($order->id);
        $orderCapture = new ViaBillOrderCapture($primary);
        $total = $order->total_paid_tax_incl;
        return $this->tools->displayNumber($orderCapture->getTotalCaptured()) < $this->tools->displayNumber($total);
    }

    /**
     * Checks If Order Can Be Refunded.
     *
     * @param Order $order
     *
     * @return bool
     */
    public function canBeRefunded(Order $order)
    {
        return $this->getRemainingToRefund($order) > 0;
    }

    /**
     * Gets Order Remaining Amount To Refund.
     *
     * @param Order $order
     *
     * @return float|int
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getRemainingToRefund(Order $order)
    {
        $idOrderCapture = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $orderCapture = new \ViaBillOrderCapture($idOrderCapture);
        $totalCaptured = $orderCapture->getTotalCaptured();

        if (!$totalCaptured) {
            return 0;
        }

        $idRefund = \ViaBillOrderRefund::getPrimaryKey($order->id);
        $refund = new \ViaBillOrderRefund($idRefund);
        return $totalCaptured - $refund->getTotalRefunded();
    }

    public function getRemainingToCapture(Order $order)
    {
        $orderTotal = $order->total_paid_tax_incl;

        $idOrderCapture = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $orderCapture = new \ViaBillOrderCapture($idOrderCapture);

        return (float) $orderTotal - (float) $orderCapture->getTotalCaptured();
    }


    /**
     * Checks If Order Can Be Renewed.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function canBeRenewed(Order $order)
    {
        $status = $this->getStatus($order);

        $orderTotal = $this->tools->displayNumber($order->total_paid_tax_incl);
        $primary = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $capture = new \ViaBillOrderCapture($primary);

        $isFullCapture = $orderTotal === $this->tools->displayNumber($capture->getTotalCaptured());
        $isValidStatus = in_array(
            $status->getStatus(),
            array(
                Config::ORDER_STATUS_APPROVED,
                Config::ORDER_STATUS_CAPTURED
            ),
            true
        );
        return $isValidStatus && !$isFullCapture;
    }

    /**
     * Gets Order Status.
     *
     * @param Order $order
     *
     * @return \ViaBill\Object\Api\Status\StatusResponse|mixed
     *
     * @throws \Exception
     */
    private function getStatus(Order $order)
    {
        $cacheKey = __CLASS__.__FUNCTION__.$order->id;

        if (\Cache::isStored($cacheKey)) {
            return \Cache::retrieve($cacheKey);
        }

        $user = $this->userService->getUser();
        $reference = $order->reference;

        $signature = $this->signaturesGenerator->generateStatusSignature(
            $user,
            $reference
        );

        $statusRequest = new StatusRequest($reference, $user->getKey(), $signature);
        $statusResponse = $this->statusService->getStatus($statusRequest);

        if ($statusResponse->hasErrors()) {
            throw new \Exception(json_encode($statusResponse->getErrorNames()));
        }

        \Cache::store($cacheKey, $statusResponse);
        return $statusResponse;
    }
}
