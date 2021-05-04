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

namespace ViaBill\Adapter;

/**
 * Class Configuration
 *
 * @package ViaBill\Adapter
 */
class Configuration
{
    /**
     * Gets Configuration By Given Name.
     *
     * @param string $name
     *
     * @return string
     */
    public function get($name)
    {
        return \Configuration::get($name);
    }

    /**
     * Sets Configuration By Given Name.
     *
     * @param string $name
     *
     * @return string
     */
    public function set($name, $value)
    {
        return \Configuration::set($name, $value);
    }

    /**
     * Updates Configuration By Given Name.
     *
     * @param string $name
     *
     * @return string
     */
    public function updateValue($name, $value)
    {
        return \Configuration::updateValue($name, $value);
    }
}
