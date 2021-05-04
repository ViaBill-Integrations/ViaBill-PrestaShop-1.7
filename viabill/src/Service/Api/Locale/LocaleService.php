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

namespace ViaBill\Service\Api\Locale;

use Cache;
use ViaBill\Adapter\Tools;
use ViaBill\Object\Api\Locale\Locale;
use ViaBill\Service\Api\ApiRequest;

/**
 * Class LocaleService
 * @package ViaBill\Service\Api\Locale
 */
class LocaleService
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
     * Gets Locale Response From API.
     *
     * @return Locale[]
     */
    public function getLocale()
    {
        $cacheKey = __CLASS__.__FUNCTION__;

        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
        }

        $response = $this->apiRequest->get('api/public/locale');
        $bodyNormalized = json_decode($response->getBody(), true);

        $result = array();
        if (!empty($bodyNormalized)) {
            foreach ($bodyNormalized as $item) {
                $locale = new Locale(
                    $item['isoCode'],
                    $item['language'],
                    $item['currencyCode']
                );

                $result[] = $locale;
            }
        }
        Cache::store($cacheKey, $result);
        return $result;
    }
}
