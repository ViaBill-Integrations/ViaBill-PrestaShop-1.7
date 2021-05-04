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

namespace ViaBill\Grid\Row;

use ViaBill\Service\Provider\OrderStatusProvider;
use Module;
use Order;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

final class CaptureAccessibilityChecker implements AccessibilityCheckerInterface
{
    /**
     * @var OrderStatusProvider
     */
    private $orderStatusProvider;

    public function __construct(OrderStatusProvider $orderStatusProvider)
    {
        $this->orderStatusProvider = $orderStatusProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $record)
    {
        $order = new Order($record['id_order']);
        $module = Module::getInstanceByName('viabill');

        if (!$module->isViabillOrder($order)) {
            return false;
        }

        if (!$this->orderStatusProvider->canBeCaptured($order)) {
            return false;
        }

        return true;
    }
}
