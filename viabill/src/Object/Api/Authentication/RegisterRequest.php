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

namespace ViaBill\Object\Api\Authentication;

use ViaBill\Config\Config;
use ViaBill\Object\Api\SerializedObjectInterface;

/**
 * Class RegisterRequest
 *
 * @package ViaBill\Object\Api\Authentification
 */
class RegisterRequest implements SerializedObjectInterface
{
    /**
     * Registration Request Email Variable Declaration.
     *
     * @var string
     */
    private $email;

    /**
     * Registration Request URL Variable Declaration.
     *
     * @var string
     */
    private $url;

    /**
     * Registration Request Country Variable Declaration.
     *
     * @var string
     */
    private $country;

    /**
     * Registration Request Additional Info Variable Declaration.
     *
     * @var string[]
     */
    private $additionalInfo;

    /**
     * RegisterRequest constructor.
     *
     * @param string $email
     * @param string $url
     * @param string $country
     * @param string[] $additionalInfo
     */
    public function __construct($email, $url, $country, array $additionalInfo)
    {
        $this->email = $email;
        $this->url = $url;
        $this->country = $country;
        $this->additionalInfo = $additionalInfo;
    }

    /**
     * Gets Register Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets Register URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets Register Country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Gets Register Additional Info.
     *
     * @return string[]
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * Gets Register Serialized Data.
     *
     * @return array
     */
    public function getSerializedData()
    {
        return array(
            'email' => $this->email,
            'url' => $this->url,
            'country' => $this->country,
            'affiliate' => Config::REGISTER_REQUEST_AFFILIATE,
            'additionalInfo' => $this->additionalInfo
        );
    }
}
