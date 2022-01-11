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

namespace ViaBill\Service\Api\Cancel;

use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\Cancel\CancelRequest;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\Api\OrderStatusApiService;
use ViaBill\Util\DebugLog;

/**
 * Class CancelService
 */
class CancelService extends OrderStatusApiService
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
     * Gets Payment Cancel API Response.
     *
     * @param CancelRequest $cancelRequest
     *
     * @return \ViaBill\Object\Api\ApiResponse|null
     */
    public function cancelPayment(CancelRequest $cancelRequest)
    {
        $serializer = $this->serializerFactory->getSerializer();

        // debug info
        $debug_str = 'Cancel Payment API Request/ [body: ' . var_export($cancelRequest, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        $apiResponse = $this->apiRequest->post(
            '/api/transaction/cancel',
            [
                'body' => $serializer->serialize($cancelRequest, 'json'),
            ]
        );

        // debug info
        $debug_str = 'Cancel Payment API Request/ [response: ' . var_export($apiResponse, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        if (in_array($apiResponse->getStatusCode(), [400, 500])) {
            return $this->getWithFormattedError($this->module, $apiResponse, $cancelRequest->getId());
        }

        return $apiResponse;
    }
}
