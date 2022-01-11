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

namespace ViaBill\Object\Api\Countries;

/**
 * Class CountryResponse
 */
class CountryResponse
{
    /**
     * County Code Variable Declaration.
     *
     * @var string
     */
    private $code;

    /**
     * County Name Variable Declaration.
     *
     * @var string
     */
    private $name;

    /**
     * CountriesResponse constructor.
     *
     * @param string $code - iso 3166 alpha 2 type
     * @param string $name
     */
    public function __construct($code, $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * Gets County Code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets County Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
