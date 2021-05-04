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

namespace ViaBill\Service\Api\Authentication;

use ViaBill\Adapter\Tools;
use ViaBill\Factory\SerializerFactory;
use ViaBill\Object\Api\Authentication\LoginRequest;
use ViaBill\Object\Api\Authentication\LoginResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Util\DebugLog;

/**
 * Class LoginService
 *
 * @package ViaBill\Service\Api\Authentication
 */
class LoginService
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
     * LoginService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param SerializerFactory $serializerFactory
     * @param Tools $tools
     */
    public function __construct(ApiRequest $apiRequest, SerializerFactory $serializerFactory, Tools $tools)
    {
        $this->apiRequest = $apiRequest;
        $this->serializerFactory = $serializerFactory;
        $this->tools = $tools;
    }

    /**
     * Gets Login Response From API.
     *
     * @param LoginRequest $loginRequest
     *
     * @return LoginResponse
     */
    public function login(LoginRequest $loginRequest)
    {
        $serializer = $this->serializerFactory->getSerializer();

        // debug info
        $debug_str = "Login API Request/ [body: ".var_export($loginRequest, true)."]";
        DebugLog::msg($debug_str, 'debug');

        $response = $this->apiRequest->post(
            '/api/addon/prestashop/login',
            array(
                'body' => $serializer->serialize($loginRequest, 'json')
            )
        );

        if ($response->getStatusCode() !== 200) {
            $er = $response->getErrors();
            $debug_str = "Login API Response/[Status code: ".$response->getStatusCode()."][Errors: ".var_export($er, true)."]";
            DebugLog::msg($debug_str, 'error');

            return new LoginResponse(
                '',
                '',
                '',
                $response->getErrors()
            );
        }

        $decodedBody = json_decode($response->getBody(), true);
        
        $debug_str = "Login API Response/ [Body: ".var_export($decodedBody, true)."]";
        DebugLog::msg($debug_str, 'debug');

        return new LoginResponse(
            $decodedBody['key'],
            $decodedBody['secret'],
            $decodedBody['pricetagScript']
        );
    }
}
