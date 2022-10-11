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

namespace ViaBill\Service\Api\Payment;

use ViaBill\Adapter\Tools;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\Payment\PaymentRequest;
use ViaBill\Object\Api\Payment\PaymentResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Util\DebugLog;

/**
 * Class PaymentService
 */
class PaymentService
{
    /**
     * API Request Variable Declaration.
     *
     * @var ApiRequest
     */
    private $apiRequest;

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
     * PaymentService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param Tools $tools
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        ApiRequest $apiRequest,
        Tools $tools,
        LoggerFactory $loggerFactory
    ) {
        $this->apiRequest = $apiRequest;
        $this->tools = $tools;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Gets Payment Response From API.
     *
     * @param PaymentRequest $paymentRequest
     *
     * @return PaymentResponse
     */
    public function createPayment(PaymentRequest $paymentRequest)
    {
        // debug info
        $debug_str = 'Payment API Request/ [body: ' . var_export($paymentRequest, true) . ']';
        DebugLog::msg($debug_str, 'debug');                        

        $apiResponse = $this->apiRequest->post(
            '/api/checkout-authorize/addon/prestashop',
            [
                'body' => json_encode($paymentRequest->getSerializedData(), JSON_UNESCAPED_SLASHES),
            ]
        );

        // debug info
        $debug_str = 'Payment API Request/ [response: ' . var_export($apiResponse, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        // transaction history info
        $transactionHistory = new \ViaBillTransactionHistory();
        $transactionHistory->createNew($paymentRequest, $apiResponse);

        if (!in_array($apiResponse->getStatusCode(), [200, 204])) {
            $logger = $this->loggerFactory->create();
            $errors = $apiResponse->getErrors();
            $errorsArray = [];

            foreach ($errors as $key => $error) {
                $errorsArray[$key] = $error->getError();
            }

            if (!empty($errorsArray)) {
                $debug_str = 'Payment API Request/ [errors: ' . var_export($errorsArray, true) . ']';
                DebugLog::msg($debug_str, 'error');
            }

            return new PaymentResponse($apiResponse->getEffectiveUrl(), $errors);
        }

        return new PaymentResponse($apiResponse->getEffectiveUrl());
    }
}
