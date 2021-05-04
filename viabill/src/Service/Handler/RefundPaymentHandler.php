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
use ViaBill\Adapter\Validate;
use ViaBill\Config\Config;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\Refund\RefundRequest;
use ViaBill\Object\Handler\HandlerResponse;
use ViaBill\Service\Api\Refund\RefundService;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;
use ViaBill\Util\DebugLog;
use Order;

/**
 * Class RefundPaymentHandler
 *
 * @package ViaBill\Service\Handler
 */
class RefundPaymentHandler
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'RefundPaymentHandler';

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Validate Variable Declaration.
     *
     * @var Validate
     */
    private $validate;

    /**
     * Refund Service Variable Declaration.
     *
     * @var RefundService
     */
    private $refundService;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * Signature Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Logger Factory Variable Declaration.
     *
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * RefundPaymentHandler constructor.
     *
     * @param \ViaBill $module
     * @param Configuration $configuration
     * @param LoggerFactory $loggerFactory
     * @param Validate $validate
     * @param Tools $tools
     * @param RefundService $refundService
     * @param UserService $userService
     * @param SignaturesGenerator $signaturesGenerator
     */
    public function __construct(
        \ViaBill $module,
        Configuration $configuration,
        LoggerFactory $loggerFactory,
        Validate $validate,
        Tools $tools,
        RefundService $refundService,
        UserService $userService,
        SignaturesGenerator $signaturesGenerator
    ) {
        $this->module = $module;
        $this->validate = $validate;
        $this->refundService = $refundService;
        $this->userService = $userService;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->tools = $tools;
        $this->loggerFactory = $loggerFactory;
        $this->configuration = $configuration;
    }

    /**
     * Handles Payment Refund.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return HandlerResponse
     */
    public function handle(Order $order, $amount)
    {
        // debug info
        $debug_str = (empty($order))?'[empty]':var_export($order, true);
        DebugLog::msg("Refund Payment Handle / Amount: $amount Order: $debug_str", "notice");

        $errors = $this->validateRefundPayment($order, $amount);

        if (!empty($errors)) {
            $debug_str = var_export($errors, true);
            DebugLog::msg("Refund Payment Handle / Validation Errors: $debug_str", "error");

            return new HandlerResponse($order, 500, $errors);
        }
        
        $user = $this->userService->getUser();
        $reference = $order->reference;
        $apiKey = $user->getKey();

        $currency = new \Currency($order->id_currency);
        $currencyIso = $currency->iso_code;

        $signature = $this->signaturesGenerator->generateRefundSignature($user, $reference, $amount, $currencyIso);

        $debug_str = '';
        $debug_str .= (!empty($reference))?"[Order Ref: ".$reference."]":"[No order reference]";
        $debug_str .= (!empty($apiKey))?"[apiKey: ".$apiKey."]":"[No apiKey]";
        $debug_str .= (!empty($signature))?"[Signature: ".$signature."]":"[No signature]";
        $debug_str .= (!empty($amount))?"[Amount: ".$amount."]":"[No amount]";
        $debug_str .= (!empty($currencyIso))?"[Currency ISO: ".$currencyIso."]":"[No currency ISO]";
        DebugLog::msg("Refund Payment Handle / Request params: $debug_str", "notice");
        
        $refundRequest = new RefundRequest(
            $reference,
            $apiKey,
            $signature,
            $amount,
            $currencyIso
        );

        $refundResponse = $this->refundService->refundPayment($refundRequest);
        $responseErrors = $refundResponse->getErrors();

        if (!empty($responseErrors)) {
            foreach ($responseErrors as $error) {
                $errors[] = $error->getError();
            }
        }

        if (!empty($responseErrors)) {
            $debug_str = var_export($responseErrors, true);
            DebugLog::msg("Refund Payment Handle / Response Errors: $debug_str", "error");
        }

        $warnings = array();
        if (empty($responseErrors)) {
            $isMarked = $this->markAsRefunded($order, $amount);

            if (!$isMarked) {
                $warnings[] = sprintf(
                    $this->module->l(
                        'Total amount of %s has been refunded but not have been marked',
                        self::FILENAME
                    )
                );
            }
        }

        $successMessage = sprintf(
            $this->module->l('Successfully refunded total amount of %s'),
            $this->tools->displayPrice($amount, $currency)
        );

        $debug_str = '';
        if (!empty($successMessage)) {
            $debug_str .= "[Message: ".var_export($successMessage, true)."]";
        }
        if (!empty($errors)) {
            $debug_str .= "[Errors: ".var_export($errors, true)."]";
        }
        if (!empty($warnings)) {
            $debug_str .= "[Warnings: ".var_export($warnings, true)."]";
        }
        $debug_str .= (method_exists($refundResponse, 'getStatusCode'))?"[Status code: ".$refundResponse->getStatusCode()."]":"[No status code]";
        DebugLog::msg("Refund Payment Handle / Response Handler Params: $debug_str", "notice");

        return new HandlerResponse(
            $order,
            $refundResponse->getStatusCode(),
            $errors,
            $successMessage,
            $warnings
        );
    }

    /**
     * Validates Payment Refund.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function validateRefundPayment(Order $order, $amount)
    {
        if (!$this->validate->isUnsignedFloat($amount)) {
            $message =
                $this->module->l(
                    'Incorrect value provided for refund fields. Expected unsigned number got %s',
                    self::FILENAME
                );

            return array(
                sprintf(
                    $message,
                    $amount
                )
            );
        }

        $idCapture = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $idRefund = \ViaBillOrderRefund::getPrimaryKey($order->id);

        $capture = new \ViaBillOrderCapture($idCapture);
        $refund = new \ViaBillOrderRefund($idRefund);

        $totalCaptured = $capture->getTotalCaptured();

        if (!$totalCaptured) {
            return array(
                $this->module->l('Order has not been captured. Refund is not possible.', self::FILENAME)
            );
        }

        $totalRefunded = $refund->getTotalRefunded();
        $refundRemaining = $totalCaptured - $totalRefunded;

        $currency = new \Currency($order->id_currency);

        if ($this->tools->displayNumber($amount) > $this->tools->displayNumber($refundRemaining)) {
            $errorMessage = sprintf(
                $this->module->l('The total amount of refund %s is greater then the remaining amount of %s'),
                $this->tools->displayPrice($amount, $currency),
                $this->tools->displayPrice($refundRemaining, $currency)
            );

            return array($errorMessage);
        }

        return array();
    }

    /**
     * Marks Refunded Payment.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function markAsRefunded(Order $order, $amount)
    {
        $viaBillRefund = new \ViaBillOrderRefund();
        $viaBillRefund->id_order = $order->id;
        $viaBillRefund->amount = (float) $amount;

        $capturePrimary = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $capture = new \ViaBillOrderCapture($capturePrimary);
        $totalCaptured = $this->tools->displayNumber($capture->getTotalCaptured());

        try {
            $viaBillRefund->save();
            $totalRefunded = $this->tools->displayNumber($viaBillRefund->getTotalRefunded());
            if ($totalCaptured === $totalRefunded) {
                $refundState = $this->configuration->get(Config::PAYMENT_REFUNDED);
                $order->setCurrentState($refundState);
            }
            $returnValue = true;
        } catch (\Exception $exception) {
            $logger = $this->loggerFactory->create();
            $logger->warning(
                'order with reference '.$order->reference.' thrown exception '.$exception->getMessage(),
                array(
                    'trace' => $exception->getTraceAsString()
                )
            );
            $returnValue = false;
        }

        return $returnValue;
    }
}
