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

namespace ViaBill\Service\Handler;

use ViaBill\Object\Api\Renew\RenewRequest;
use ViaBill\Object\Handler\HandlerResponse;
use ViaBill\Service\Api\Renew\RenewService;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;
use ViaBill\Util\DebugLog;
use Order;
use ViaBill;

/**
 * Class RenewPaymentHandler
 *
 * @package ViaBill\Service\Handler
 */
class RenewPaymentHandler
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'RenewPaymentHandler';

    /**
     * Renew Service Variable Declaration.
     *
     * @var RenewService
     */
    private $renewService;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * Signatures Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    private $module;

    /**
     * RenewPaymentHandler constructor.
     *
     * @param ViaBill $module
     * @param RenewService $renewService
     * @param UserService $userService
     * @param SignaturesGenerator $signaturesGenerator
     */
    public function __construct(
        ViaBill $module,
        RenewService $renewService,
        UserService $userService,
        SignaturesGenerator $signaturesGenerator
    ) {
        $this->renewService = $renewService;
        $this->userService = $userService;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->module = $module;
    }

    /**
     * Handles Payment Renewal.
     *
     * @param Order $order
     *
     * @return HandlerResponse
     */
    public function handle(Order $order)
    {
        // debug info
        $debug_str = (empty($order))?'[empty]':var_export($order, true);
        DebugLog::msg("Renew Payment Handle / Order: $debug_str", "notice");

        $reference = $order->reference;
        $user = $this->userService->getUser();
        $signature = $this->signaturesGenerator->generateRenewSignature(
            $user,
            $reference
        );

        $debug_str = '';
        $debug_str .= (!empty($reference))?"[Order Ref: ".$reference."]":"[No order reference]";
        $debug_str .= (method_exists($user, 'getKey'))?"[Key: ".$user->getKey()."]":"[No user key]";
        $debug_str .= (!empty($signature))?"[Signature: ".$signature."]":"[No signature]";
        DebugLog::msg("Renew Payment Handle / Request: $debug_str", "notice");

        $renewRequest = new RenewRequest(
            $reference,
            $user->getKey(),
            $signature
        );

        $renewResponse = $this->renewService->renewPayment($renewRequest);
        $apiErrors = $renewResponse->getErrors();

        $errors = array();
        if (!empty($apiErrors)) {
            foreach ($apiErrors as $error) {
                $errors[] = $error->getError();
            }

            $debug_str = var_export($apiErrors, true);
            DebugLog::msg("Renew Payment Handle / Respose Errors: $debug_str", "error");
        }
                
        return new HandlerResponse(
            $order,
            $renewResponse->getStatusCode(),
            $errors,
            $this->module->l('Order has been Successfully renewed')
        );
    }
}
