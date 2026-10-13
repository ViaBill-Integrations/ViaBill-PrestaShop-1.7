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
     * Seconds to wait for a connection to the ViaBill API.
     */
    const CONNECT_TIMEOUT = 10;

    /**
     * Seconds to wait for a complete ViaBill API response.
     */
    const TIMEOUT = 30;

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
        // Never wait indefinitely for the ViaBill API (it is called during
        // checkout), and always verify its TLS certificate.
        $requestOptions = [
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'timeout' => self::TIMEOUT,
            'verify' => true,
        ];

        if (Config::isVersionAbove8()) {
            $config = [
                'base_uri' => $this->config->getBaseUrl(),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ] + $requestOptions;
        } else {
            $config = [
                'base_url' => $this->config->getBaseUrl(),
                'defaults' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ] + $requestOptions,
            ];
        }
        
        return new Client($config);
    }
}
