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

namespace ViaBill\Service\Api\Countries;

use ViaBill\Adapter\Tools;
use ViaBill\Object\Api\Countries\CountryResponse;
use ViaBill\Service\Api\ApiRequest;

/**
 * Class CountryService
 */
class CountryService
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
     * CountryService constructor.
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
     * Gets Countries API Response.
     *
     * @param string $locale - iso 3166 alpha 2 type
     *
     * @return CountryResponse[]
     *
     * @throws \Exception
     */
    public function getCountries($locale)
    {
        $requestUrl = sprintf('/api/addon/prestashop/countries/supported/%s', $locale);
        $apiResponse = $this->apiRequest->get($requestUrl);

        if ($apiResponse->getStatusCode() !== 200) {
            return [];
        }

        $body = json_decode($apiResponse->getBody(), true);

        if (empty($body)) {
            return [];
        }

        $result = [];
        foreach ($body as $data) {
            $countryResponse = new CountryResponse($data['code'], $data['name']);
            $result[] = $countryResponse;
        }

        return $result;
    }
}
