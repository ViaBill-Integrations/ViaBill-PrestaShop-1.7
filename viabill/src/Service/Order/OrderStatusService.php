<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Service\Order;

use OrderState;
use ViaBill;
use ViaBill\Adapter\Configuration;
use ViaBill\Config\Config;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\CallBack\CallBackResponse;

/**
 * Class OrderStatusService
 */
class OrderStatusService
{
    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Logger Factory Variable Declaration.
     *
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var ViaBill
     */
    private $module;

    /**
     * OrderStatusService constructor.
     *
     * @param Configuration $configuration
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(Configuration $configuration, LoggerFactory $loggerFactory, ViaBill $module)
    {
        $this->configuration = $configuration;
        $this->loggerFactory = $loggerFactory;
        $this->module = $module;
    }

    /**
     * Changes Order Status By Callback.
     *
     * PATCH: this method now implements a small state machine instead of
     * blindly applying whatever the callback says:
     *
     *  - APPROVED never overrides an order that is already cancelled or
     *    refunded (late / duplicated / replayed callbacks used to flip a
     *    cancelled order back to "Payment accepted").
     *  - APPROVED is idempotent: an order already accepted or completed is
     *    left untouched.
     *  - CANCELLED / REJECTED never downgrade an order that has already been
     *    accepted, completed or refunded; such conflicts are logged for
     *    manual review instead.
     *  - Status comparison is trimmed and case-insensitive.
     *  - The default (unknown status) branch now resolves the error state id
     *    through Configuration::get() - previously the raw constant string
     *    'PS_OS_ERROR' was passed to setCurrentState(), which casts to state
     *    id 0 and corrupted the order history. It also only applies the error
     *    state to orders still pending.
     *
     * @param CallBackResponse $response
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function changeOrderStatusByCallBack(CallBackResponse $response)
    {
        $order = new \Order((int) $response->getOrderNumber());

        $logger = $this->loggerFactory->create();

        $status = strtoupper(trim((string) $response->getStatus()));

        $currentState = (int) $order->getCurrentState();
        $pendingState = (int) $this->configuration->get(Config::PAYMENT_PENDING);
        $acceptedState = (int) $this->configuration->get(Config::PAYMENT_ACCEPTED);
        $completedState = (int) $this->configuration->get(Config::PAYMENT_COMPLETED);
        $canceledState = (int) $this->configuration->get(Config::PAYMENT_CANCELED);
        $refundedState = (int) $this->configuration->get(Config::PAYMENT_REFUNDED);

        switch ($status) {
            case Config::CALLBACK_STATUS_SUCCESS:
                // Never re-open an order the shop already considers
                // cancelled or refunded.
                if (in_array($currentState, [$canceledState, $refundedState], true)) {
                    $logger->error(
                        'Ignoring APPROVED callback: order is already cancelled or refunded on the shop side',
                        [
                            'order' => (int) $order->id,
                            'currentState' => $currentState,
                            'callback' => $response,
                        ]
                    );

                    break;
                }

                // Idempotency: nothing to do if the order is already
                // accepted or completed.
                if (in_array($currentState, [$acceptedState, $completedState], true)) {
                    break;
                }

                $this->markOrderAsViaBillOrder($order, $response, $logger);

                $order->setCurrentState($acceptedState);

                break;
            case Config::CALLBACK_STATUS_CANCEL:
            case Config::CALLBACK_STATUS_REJECTED:
                // Idempotency: already cancelled.
                if ($currentState === $canceledState) {
                    break;
                }

                // Never downgrade an order that has already been accepted,
                // completed or refunded. Log for manual review instead.
                if (in_array($currentState, [$acceptedState, $completedState, $refundedState], true)) {
                    $logger->error(
                        'Ignoring CANCELLED/REJECTED callback: order has already been approved on the shop side',
                        [
                            'order' => (int) $order->id,
                            'currentState' => $currentState,
                            'callback' => $response,
                        ]
                    );

                    break;
                }

                $order->setCurrentState($canceledState);

                break;
            default:
                $logger->error(
                    'Unexpected state detected',
                    ['callback' => $response]
                );

                // Only move orders that are still pending into the error
                // state; never touch orders in any other state because of an
                // unrecognized callback payload.
                if ($currentState === $pendingState) {
                    $errorState = (int) $this->configuration->get(Config::PAYMENT_ERROR);

                    if ($errorState) {
                        $order->setCurrentState($errorState);
                    }
                }
        }
    }

    /**
     * Persists the ViaBill order marker (used by capture/refund tooling),
     * avoiding duplicate rows when callbacks are retried.
     *
     * @param \Order $order
     * @param CallBackResponse $response
     * @param mixed $logger
     */
    private function markOrderAsViaBillOrder(\Order $order, CallBackResponse $response, $logger)
    {
        try {
            $existingId = \ViaBillOrder::getPrimaryKey((int) $order->id);
            if ($existingId) {
                return;
            }

            $viaBillOrder = new \ViaBillOrder();
            $viaBillOrder->id_order = (int) $order->id;
            $viaBillOrder->id_currency = (int) $order->id_currency;
            $viaBillOrder->save();
        } catch (\Exception $exception) {
            $logger->error(
                'successful request but order marking failed',
                [
                    'exceptionMessage' => $exception->getMessage(),
                    'callback' => $response,
                ]
            );
        }
    }

    public function getOrderStatusesForMultiselect()
    {
        $orderStatuses = (array) OrderState::getOrderStates(\Context::getContext()->language->id);
        $selectedOrderStatusIds = $this->getDecodedCaptureMultiselectOrderStatuses();

        $multiselectOrderStatuses = [];

        foreach ($orderStatuses as $orderStatus) {
            if (!isset($orderStatus['id_order_state'])) {
                continue;
            }

            if ((int) $orderStatus['id_order_state'] === (int) $this->configuration->get(Config::PAYMENT_PENDING)) {
                continue;
            }

            $selected = false;

            if (in_array($orderStatus['id_order_state'], $selectedOrderStatusIds)) {
                $selected = true;
            }

            $multiselectOrderStatuses[] = [
                'id_order_state' => $orderStatus['id_order_state'],
                'name' => $orderStatus['name'],
                'selected' => $selected,
            ];
        }

        return $multiselectOrderStatuses;
    }

    public function getDecodedCaptureMultiselectOrderStatuses()
    {
        return (array) json_decode($this->configuration->get(Config::CAPTURE_ORDER_STATUS_MULTISELECT));
    }

    public function setEncodedCaptureMultiselectOrderStatuses($multiselectOrderStatuses)
    {
        $encodedMultiselectOrderStatuses = json_encode($multiselectOrderStatuses);

        if (!$this->configuration->updateValue(
            Config::CAPTURE_ORDER_STATUS_MULTISELECT,
            $encodedMultiselectOrderStatuses
        )) {
            return false;
        }

        return true;
    }
}
