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

namespace ViaBill\Service\Api\Notification;

use ViaBill\Adapter\Tools;
use ViaBill\Object\Api\Notification\NotificationResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\UserService;
use ViaBill\Util\DebugLog;

/**
 * Class NotificationService
 *
 * @package ViaBill\Service\Api\Notification
 */
class NotificationService
{
    /**
     * API Request Variable Declaration.
     *
     * @var ApiRequest
     */
    private $apiRequest;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * NotificationService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param UserService $userService
     * @param Tools $tools
     */
    public function __construct(ApiRequest $apiRequest, UserService $userService, Tools $tools)
    {
        $this->apiRequest = $apiRequest;
        $this->userService = $userService;
        $this->tools = $tools;
    }

    /**
     * Gets Notification Response From API.
     *
     * @return NotificationResponse[]
     *
     * @throws \Exception
     */
    public function getNotifications()
    {
        $user = $this->userService->getUser();
        $requestUrl =
            sprintf(
                '/api/addon/prestashop/notifications?key=%s&signature=%s&platform=%s',
                $user->getKey(),
                $user->getSignature(),
                $this->getPlatformInfo()
            );

        // debug info
        $debug_str = "Notifications API Request/ [url: ".var_export($requestUrl, true)."]";
        DebugLog::msg($debug_str, 'debug');

        $apiResponse = $this->apiRequest->get($requestUrl);

        // debug info
        $debug_str = "Notifications API Request/ [response: ".var_export($apiResponse, true)."]";
        DebugLog::msg($debug_str, 'debug');

        if ($apiResponse->getStatusCode() !== 200) {
            return array();
        }

        $decodedResult = json_decode($apiResponse->getBody(), true);

        if (empty($decodedResult['messages'])) {
            return array();
        }
        
        $result = array();
        foreach ($decodedResult['messages'] as $message) {
            $result[] = new NotificationResponse($message);
        }

        return $result;
    }

    /**
     * Get platform info used with the notifications call.
     *
     * @return string
     */
    private function getPlatformInfo() {      
        $info = 'prestashop';
        
        $platform_version = \Configuration::get('PS_VERSION_DB');
        if (!empty($platform_version)) {
            $info .= '&platform_ver='.$platform_version;
        }

        $moduleInstance = \Module::getInstanceByName('viabill');        
        $module_version = $moduleInstance->version;
        if (!empty($module_version)) {
            $info .= '&module_ver='.$module_version;
        }
        
        return $info;             
    }
}
