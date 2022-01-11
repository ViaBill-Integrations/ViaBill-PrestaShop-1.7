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

namespace ViaBill\Service\Api\Capture;

use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\Capture\CaptureRequest;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\Api\OrderStatusApiService;
use ViaBill\Util\DebugLog;

/**
 * Class CaptureService
 */
class CaptureService extends OrderStatusApiService
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
     * Gets Transaction Capture API Response.
     *
     * @param CaptureRequest $captureRequest
     *
     * @return ApiResponse|null
     */
    public function captureTransaction(CaptureRequest $captureRequest)
    {
        $serializer = $this->serializerFactory->getSerializer();

        // debug info
        $debug_str = 'Capture Payment API Request/ [body: ' . var_export($captureRequest, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        $apiResponse = $this->apiRequest->post(
            '/api/transaction/capture',
            [
                'body' => $serializer->serialize($captureRequest, 'json'),
            ]
        );

        // debug info
        $debug_str = 'Capture Payment API Request/ [response: ' . var_export($apiResponse, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        if (in_array($apiResponse->getStatusCode(), [400, 403, 409, 500])) {
            return $this->getWithFormattedError($this->module, $apiResponse, $captureRequest->getId());
        }

        return $apiResponse;
    }
}
