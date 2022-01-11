<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Service\Api\Link;

use ViaBill\Object\Api\Link\LinkRequest;
use ViaBill\Object\Api\Link\LinkResponse;
use ViaBill\Service\Api\ApiRequest;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;

/**
 * Class RegisterService
 * Gets Auto Login Link From ViaBill API.
 */
class LinkService
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
     * Signatures Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * LinkService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param UserService $userService
     * @param SignaturesGenerator $signaturesGenerator
     */
    public function __construct(
        ApiRequest $apiRequest,
        UserService $userService,
        SignaturesGenerator $signaturesGenerator
    ) {
        $this->apiRequest = $apiRequest;
        $this->userService = $userService;
        $this->signaturesGenerator = $signaturesGenerator;
    }

    /**
     * Gets Link Response From API.
     *
     * @return LinkResponse
     */
    public function getLink()
    {
        $linkRequest = $this->getLinkRequest();

        $requestUrl =
            sprintf(
                '/api/addon/prestashop/myviabill?key=%s&signature=%s',
                $linkRequest->getKey(),
                $linkRequest->getSignature()
            );

        $response = $this->apiRequest->get($requestUrl);

        if ($response->getStatusCode() !== 200) {
            return new LinkResponse(
                '',
                $response->getErrors()
            );
        }

        $decodedBody = json_decode($response->getBody(), true);

        return new LinkResponse($decodedBody['url']);
    }

    /**
     * Gets Request Data Fot Auto Login Link.
     *
     * @return LinkRequest
     */
    private function getLinkRequest()
    {
        $user = $this->userService->getUser();
        $signature = $this->signaturesGenerator->generateSignature(
            $user
        );

        $linkKey = $user->getKey();
        $linkSignature = $signature;

        return new LinkRequest($linkKey, $linkSignature);
    }
}
