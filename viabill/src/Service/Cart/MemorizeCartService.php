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

namespace ViaBill\Service\Cart;

use Cart;
use ViaBill\Repository\ReadOnlyRepositoryInterface;
use ViaBillPendingOrderCart;
use Order;

/**
 * Memorizes the cart
 */
class MemorizeCartService
{
    private $orderCartAssociationService;
    private $pendingOrderCartRepository;

    public function __construct(
        OrderCartAssociationService $orderCartAssociationService,
        ReadOnlyRepositoryInterface $pendingOrderCartRepository
    ) {
        $this->orderCartAssociationService = $orderCartAssociationService;
        $this->pendingOrderCartRepository = $pendingOrderCartRepository;
    }

    public function memorizeCart(Order $toBeProcessedOrder)
    {
        // create a pending cart so we can repeat the process once again
        $this->orderCartAssociationService->createPendingCart($toBeProcessedOrder);
    }

    public function removeMemorizedCart(Order $successfulProcessedOrder)
    {
        /** @var ViaBillPendingOrderCart|null $pendingOrderCart */
        $pendingOrderCart = $this->pendingOrderCartRepository->findOneBy([
            'order_id' => $successfulProcessedOrder->id
        ]);

        if (null === $pendingOrderCart) {
            return;
        }

        $cart = new Cart($pendingOrderCart->cart_id);

        if (!\Validate::isLoadedObject($cart)) {
            return;
        }

        $cart->delete();
    }
}
