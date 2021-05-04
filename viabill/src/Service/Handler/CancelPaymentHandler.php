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

use ViaBill\Adapter\Configuration;
use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use ViaBill\Object\Api\Cancel\CancelRequest;
use ViaBill\Object\Handler\HandlerResponse;
use ViaBill\Service\Api\Cancel\CancelService;
use ViaBill\Service\Provider\OrderStatusProvider;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;
use ViaBill\Util\DebugLog;
use Order;

/**
 * Class CancelPaymentHandler
 *
 * @package ViaBill\Service\Handler
 */
class CancelPaymentHandler
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'CancelPaymentHandler';

    /**
     * Cancel Service Variable Declaration.
     *
     * @var CancelService
     */
    private $cancelService;

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
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Order State Provider Variable Declaration.
     *
     * @var OrderStatusProvider
     */
    private $stateProvider;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * CancelPaymentHandler constructor.
     *
     * @param \ViaBill $module
     * @param CancelService $cancelService
     * @param UserService $userService
     * @param SignaturesGenerator $signaturesGenerator
     * @param Configuration $configuration
     * @param OrderStatusProvider $stateProvider
     * @param Tools $tools
     */
    public function __construct(
        \ViaBill $module,
        CancelService $cancelService,
        UserService $userService,
        SignaturesGenerator $signaturesGenerator,
        Configuration $configuration,
        OrderStatusProvider $stateProvider,
        Tools $tools
    ) {
        $this->cancelService = $cancelService;
        $this->userService = $userService;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->configuration = $configuration;
        $this->module = $module;
        $this->stateProvider = $stateProvider;
        $this->tools = $tools;
    }

    /**
     * Handles Payment Cancel.
     *
     * @param Order $order
     *
     * @return HandlerResponse
     */
    public function handle(Order $order)
    {
        $cancelState = $this->configuration->get(Config::PAYMENT_CANCELED);
        $errors = array();
        $isCancelled = false;

        // debug info
        $debug_str = (empty($order))?'[empty]':var_export($order, true);
        DebugLog::msg("Cancel Payment Handle / Order: $debug_str", "notice");

        try {
            $isCancelled = $this->stateProvider->isCancelled($order);
        } catch (\Exception $exception) {
            /** @var string[] $errors */
            $exception_errors = json_decode($exception->getMessage());

            if (is_array($exception_errors)) {
                foreach ($exception_errors as $error) {
                    $errors[] = $error;
                }
            } else {
                $errors[] = $exception_errors;
            }
        }

        if ($order->getCurrentState() == $cancelState || $isCancelled) {
            $errors[] = $this->module->l('Order is already canceled', self::FILENAME);
        }

        if (!empty($errors)) {
            $debug_str = var_export($errors, true);
            DebugLog::msg("Cancel Payment Handle / Errors: $debug_str", "error");
        }
        
        $user = $this->userService->getUser();

        $signature = $this->signaturesGenerator->generateCancelSignature($user, $order->reference);
                
        try {
            $debug_str = '';
            $debug_str .= (property_exists($order, 'reference'))?"[Order Ref: ".$order->reference."]":"[No order reference]";
            $debug_str .= (method_exists($user, 'getKey'))?"[Key: ".$user->getKey()."]":"[No user key]";
            $debug_str .= (!empty($signature))?"[Signature: ".$signature."]":"[No signature]";
            DebugLog::msg("Cancel Payment Handle / Request: $debug_str", "notice");
        } catch (\Exception $exception) {
            $er = $exception->getMessage();
            $debug_str = var_export($er, true);
            DebugLog::msg("Cancel Payment Handle / Request errors: $debug_str", "error");
        }
                
        $cancelRequest = new CancelRequest(
            $order->reference,
            $user->getKey(),
            $signature
        );

        $response = $this->cancelService->cancelPayment($cancelRequest);
        $responseErrors = $response->getErrors();

        if (!empty($responseErrors)) {
            foreach ($responseErrors as $error) {
                $errors[] = $error->getError();
            }

            $debug_str = var_export($responseErrors, true);
            DebugLog::msg("Cancel Payment Handle / Respose Errors: $debug_str", "error");
        } else {
            $debug_str = $response->getStatusCode();
            DebugLog::msg("Cancel Payment Handle / Respose Code: $debug_str", "notice");
        }
        
        if (empty($errors)) {
            $order->setCurrentState($cancelState);
        }

        return new HandlerResponse(
            $order,
            $response->getStatusCode(),
            $errors,
            $this->module->l('Order has been successfully canceled', self::FILENAME)
        );
    }
}
