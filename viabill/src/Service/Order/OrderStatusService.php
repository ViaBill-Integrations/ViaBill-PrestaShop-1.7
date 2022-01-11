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
     * @param CallBackResponse $response
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function changeOrderStatusByCallBack(CallBackResponse $response)
    {
        $order = new \Order((int) $response->getOrderNumber());

        $logger = $this->loggerFactory->create();

        switch ($response->getStatus()) {
            case Config::CALLBACK_STATUS_SUCCESS:
                $acceptedState = $this->configuration->get(Config::PAYMENT_ACCEPTED);

                $viaBillOrder = new \ViaBillOrder();
                $viaBillOrder->id_order = (int) $response->getOrderNumber();
                $viaBillOrder->id_currency = (int) $order->id_currency;
                try {
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

                if ($order->getCurrentState() != $acceptedState) {
                    $order->setCurrentState($acceptedState);
                }

                break;
            case Config::CALLBACK_STATUS_CANCEL:
                $order->setCurrentState($this->configuration->get(Config::PAYMENT_CANCELED));
                break;
            case Config::CALLBACK_STATUS_REJECTED:
                $order->setCurrentState($this->configuration->get(Config::PAYMENT_CANCELED));
                break;
            default:
                $logger->error(
                    'Unexpected state detected',
                    ['callback' => $response]
                );
                $order->setCurrentState(Config::PAYMENT_ERROR);
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
