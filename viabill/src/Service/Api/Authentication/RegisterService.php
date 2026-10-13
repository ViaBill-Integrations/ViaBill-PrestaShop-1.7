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

namespace ViaBill\Service\Api\Authentication;

use ViaBill\Adapter\Tools;
use ViaBill\Object\Api\Authentication\RegisterRequest;
use ViaBill\Object\Api\Authentication\RegisterResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Util\DebugLog;

/**
 * Class RegisterService
 */
class RegisterService
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
     * LoginService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param Tools $tools
     */
    public function __construct(ApiRequest $apiRequest, Tools $tools)
    {
        $this->apiRequest = $apiRequest;
        $this->tools = $tools;
    }

    /**
     * Gets Registration Response From API.
     *
     * @param RegisterRequest $registerRequest
     *
     * @return RegisterResponse
     */
    public function register(RegisterRequest $registerRequest)
    {
        // debug info
        $debug_str = 'Register API Request/ [body: ' . DebugLog::export($registerRequest) . ']';
        DebugLog::msg($debug_str, 'debug');

        $response = $this->apiRequest->post(
            '/api/addon/prestashop/register',
            [
                'body' => json_encode($registerRequest->getSerializedData(), JSON_UNESCAPED_SLASHES),
            ]
        );

        if ($response->getStatusCode() !== 200) {
            $er = $response->getErrors();
            $debug_str = 'Register API Response/[Status code: ' . $response->getStatusCode() . '][Errors: ' . DebugLog::export($er) . ']';
            DebugLog::msg($debug_str, 'error');

            return new RegisterResponse(
                '',
                '',
                '',
                $response->getErrors()
            );
        }

        $decodedBody = json_decode($response->getBody(), true);

        $debug_str = 'Register API Response/ [Status code: ' . $response->getStatusCode() . '][Body: ' . DebugLog::export($decodedBody) . ']';
        DebugLog::msg($debug_str, 'debug');

        return new RegisterResponse(
            $decodedBody['key'],
            $decodedBody['secret'],
            $decodedBody['pricetagScript']
        );
    }
}
