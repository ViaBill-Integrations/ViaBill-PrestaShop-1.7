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

namespace ViaBill\Grid\Row;

use Module;
use Order;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use ViaBill\Service\Provider\OrderStatusProvider;

final class CancelAccessibilityChecker implements AccessibilityCheckerInterface
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

        if (!$this->orderStatusProvider->canBeCancelled($order)) {
            return false;
        }

        return true;
    }
}
