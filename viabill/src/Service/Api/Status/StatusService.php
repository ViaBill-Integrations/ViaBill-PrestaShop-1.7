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

namespace ViaBill\Service\Api\Status;

use ViaBill\Adapter\Tools;
use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\Status\StatusRequest;
use ViaBill\Object\Api\Status\StatusResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Util\DebugLog;

/**
 * Class StatusService
 */
class StatusService
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
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * CaptureService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param SerializerFactory $serializerFactory
     * @param Tools $tools
     */
    public function __construct(
        ApiRequest $apiRequest,
        SerializerFactory $serializerFactory,
        Tools $tools
    ) {
        $this->apiRequest = $apiRequest;
        $this->serializerFactory = $serializerFactory;
        $this->tools = $tools;
    }

    /**
     * Gets Status Response From API.
     *
     * @param StatusRequest $statusRequest
     *
     * @return StatusResponse
     */
    public function getStatus(StatusRequest $statusRequest)
    {
        $requestUrl =
            sprintf(
                '/api/transaction/status?id=%s&apikey=%s&signature=%s',
                $statusRequest->getTransaction(),
                $statusRequest->getApiKey(),
                $statusRequest->getSignature()
            );

        // debug info
        $debug_str = 'Status API Request/ [URL: ' . var_export($requestUrl, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        $response = $this->apiRequest->get($requestUrl);
        $body = json_decode($response->getBody(), true);

        // debug info
        $debug_str = 'Status API Request/ [Response: ' . var_export($response, true) . ']';
        DebugLog::msg($debug_str, 'debug');

        return new StatusResponse($body['state'], $response->getErrors());
    }
}
