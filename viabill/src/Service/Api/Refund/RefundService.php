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

namespace ViaBill\Service\Api\Refund;

use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\Refund\RefundRequest;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\Api\OrderStatusApiService;
use ViaBill\Util\DebugLog;

/**
 * Class RefundService
 */
class RefundService extends OrderStatusApiService
{
    /**
     * API Request Variable Declaration.
     *
     * @var ApiRequest
     */
    private $apiRequest;

    /**
     * Serializer Factory Variable Declaration.
     *
     * @var SerializerFactory
     */
    private $serializerFactory;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * CaptureService constructor.
     *
     * @param \ViaBill $module
     * @param ApiRequest $apiRequest
     * @param SerializerFactory $serializerFactory
     */
    public function __construct(
        \ViaBill $module,
        ApiRequest $apiRequest,
        SerializerFactory $serializerFactory
    ) {
        $this->apiRequest = $apiRequest;
        $this->serializerFactory = $serializerFactory;
        $this->module = $module;
    }

    /**
     * Gets Payment Refund Response From API.
     *
     * @param RefundRequest $refundRequest
     *
     * @return ApiResponse|null
     */
    public function refundPayment(RefundRequest $refundRequest)
    {
        $serializer = $this->serializerFactory->getSerializer();

        // debug info
        $debug_str = 'Refund Payment API Request/ [body: ' . var_export($refundRequest, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        $apiResponse = $this->apiRequest->post(
            '/api/transaction/refund',
            [
                'body' => $serializer->serialize($refundRequest, 'json'),
            ]
        );

        // debug info
        $debug_str = 'Refund Payment API Request/ [response: ' . var_export($apiResponse, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        if (in_array($apiResponse->getStatusCode(), [400, 403, 500])) {
            return $this->getWithFormattedError($this->module, $apiResponse, $refundRequest->getId());
        }

        return $apiResponse;
    }
}
