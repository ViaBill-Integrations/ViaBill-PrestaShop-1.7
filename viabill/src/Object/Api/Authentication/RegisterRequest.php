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

namespace ViaBill\Object\Api\Authentication;

use ViaBill\Config\Config;
use ViaBill\Object\Api\SerializedObjectInterface;

/**
 * Class RegisterRequest
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
     * Registration Request Name Variable Declaration.
     *
     * @var string
     */
    private $name;

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
     * Registration Request Tax ID Declaration.
     *
     * @var string
     */
    private $taxId;

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
     * @param string $name
     * @param string $url
     * @param string $country
     * @param string $taxId
     * @param string[] $additionalInfo
     */
    public function __construct($email, $name, $url, $country, $taxId, array $additionalInfo)
    {
        $this->email = $email;
        $this->name = $name;
        $this->url = $url;
        $this->country = $country;
        $this->taxId = $taxId;
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
     * Gets Register Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Gets Register Tax ID.
     *
     * @return string
     */
    public function getTaxId()
    {
        return $this->taxId;
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
        return [
            'email' => $this->email,
            'name' => $this->name,
            'url' => $this->url,
            'country' => $this->country,
            'taxId' => $this->taxId,
            'affiliate' => Config::REGISTER_REQUEST_AFFILIATE,
            'additionalInfo' => $this->additionalInfo,
        ];
    }
}
