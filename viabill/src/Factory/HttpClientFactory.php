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

namespace ViaBill\Factory;

use GuzzleHttp\Client;
use ViaBill\Config\Config;

/**
 * Class HttpClientFactory
 */
class HttpClientFactory
{
    /**
     * Config Variable Declaration.
     *
     * @var Config
     */
    private $config;

    /**
     * HttpClientFactory constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Gets Guzzle HTTP Client.
     *
     * @return Client
     */
    public function getClient()
    {
        if (Config::isVersionAbove8()) {
            $config = [
                'base_uri' => $this->config->getBaseUrl(),            
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ];
        } else {
            $config = [
                'base_url' => $this->config->getBaseUrl(),
                'defaults' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ],
            ];
        }
        
        return new Client($config);
    }
}
