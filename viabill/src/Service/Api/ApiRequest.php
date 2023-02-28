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

namespace ViaBill\Service\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use ViaBill\Adapter\Tools;
use ViaBill\Factory\HttpClientFactory;
use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\ApiResponseError;
use ViaBill\Config\Config;

/**
 * Class ApiRequest
 */
class ApiRequest
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * HTTP Client Factory Variable Declaration.
     *
     * @var HttpClientFactory
     */
    private $clientFactory;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * ApiRequest constructor.
     *
     * @param \ViaBill $module
     * @param HttpClientFactory $clientFactory
     * @param Tools $tools
     */
    public function __construct(\ViaBill $module, HttpClientFactory $clientFactory, Tools $tools)
    {
        $this->module = $module;
        $this->clientFactory = $clientFactory;
        $this->tools = $tools;
    }

    /**
     * API Request Post Method.
     *
     * @param string $url
     * @param array $params
     *
     * @return ApiResponse|null
     */
    public function post($url, $params = [])
    {
        $response = null;
        $body = '';
        $errors = [];
        $effectiveUrl = '';

        try {
            if (Config::isVersionAbove8()) {
                // Use this method to retrieve the "effective URL"
                // which could be the redirect URL
                $params['on_stats'] = function (TransferStats $stats) use (&$effectiveUrl) {
                    $effectiveUrl = $stats->getEffectiveUri();				               
                };	
            }

            $response = $this->clientFactory->getClient()->post($url, $params);

            if ($response->getBody()) {
                $body = $response->getBody()->__toString();
            }

            $statusCode = $response->getStatusCode();            
            if (!Config::isVersionAbove8()) {
                $effectiveUrl = $response->getEffectiveUrl();
            }
        } catch (ClientException $clientException) {
            $errorBody = $clientException->getResponse()->getBody() ?
                $clientException->getResponse()->getBody()->__toString() :
                '';

            $statusCode = $clientException->getCode();
            $errors = $this->getResponseErrors($errorBody, $clientException->getMessage());
        } catch (RequestException $requestException) {
            $statusCode = $requestException->getCode();
            $errors = $this->getResponseErrors(
                '',
                $this->module->l('ViaBill service is down at the moment. Please wait and refresh the page or contact ViaBill support at merchants@viabill.com')
            );
        } catch (\Exception $exception) {
            $statusCode = $exception->getCode();
            $errors = $this->getResponseErrors('', $exception->getMessage());
        }

        return new ApiResponse($statusCode, $body, $errors, $effectiveUrl);
    }

    /**
     * API Request Get Method.
     *
     * @param string $url
     * @param array $options
     *
     * @return ApiResponse
     */
    public function get($url, $options = [])
    {
        $response = null;
        $body = '';
        $errors = [];

        try {
            $response = $this->clientFactory->getClient()->get($url, $options);

            if ($response->getBody()) {
                $body = $response->getBody()->__toString();
            }

            $statusCode = $response->getStatusCode();
        } catch (ClientException $clientException) {
            $errorBody = $clientException->getResponse()->getBody() ?
                $clientException->getResponse()->getBody()->__toString() :
                '';

            $statusCode = $clientException->getCode();
            $errors = $this->getResponseErrors($errorBody, $clientException->getMessage());
        } catch (RequestException $requestException) {
            $statusCode = $requestException->getCode();
            $errors = $this->getResponseErrors(
                '',
                $this->module->l('ViaBill service is down at the moment. Please wait and refresh the page or contact ViaBill support at merchants@viabill.com')
            );
        } catch (\Exception $exception) {
            $statusCode = $exception->getCode();
            $errors = $this->getResponseErrors('', $exception->getMessage());
        }

        return new ApiResponse($statusCode, $body, $errors);
    }

    /**
     * Gets API Request Response Errors.
     *
     * @param string $body
     * @param string $exceptionMessage
     *
     * @return array
     */
    private function getResponseErrors($body, $exceptionMessage)
    {
        $normalizedBody = json_decode($body, true);
        if (empty($normalizedBody['errors']) || !isset($normalizedBody['errors'])) {
            return [new ApiResponseError('', $exceptionMessage)];
        }
        $errors = $normalizedBody['errors'];
        $result = [];

        foreach ($errors as $error) {
            $result[] = new ApiResponseError(
                (string) $error['field'],
                (string) $error['error']
            );
        }

        return $result;
    }
}
