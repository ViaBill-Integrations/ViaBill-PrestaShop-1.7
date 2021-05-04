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

namespace ViaBill\Service\Api\Renew;

use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\ApiResponseError;
use ViaBill\Object\Api\Renew\RenewRequest;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\Api\OrderStatusApiService;
use ViaBill\Util\DebugLog;

/**
 * Class RenewService
 *
 * @package ViaBill\Service\Api\Renew
 */
class RenewService extends OrderStatusApiService
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
     * Gets Payment Renew Response From API.
     *
     * @param RenewRequest $renewRequest
     *
     * @return ApiResponse|null
     */
    public function renewPayment(RenewRequest $renewRequest)
    {
        $serializer = $this->serializerFactory->getSerializer();

        // debug info
        $debug_str = "Renew Payment API Request/ [body: ".var_export($renewRequest, true)."]";
        DebugLog::msg($debug_str, 'debug');

        $apiResponse = $this->apiRequest->post(
            '/api/transaction/renew',
            array(
                'body' => $serializer->serialize($renewRequest, 'json')
            )
        );
        
        // debug info
        $debug_str = "Renew Payment API Request/ [response: ".var_export($apiResponse, true)."]";
        DebugLog::msg($debug_str, 'debug');

        if (in_array($apiResponse->getStatusCode(), array(400, 403, 500))) {
            return $this->getWithFormattedError($this->module, $apiResponse, $renewRequest->getId());
        }
        
        return $apiResponse;
    }
}
