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

namespace ViaBill\Adapter;

/**
 * Class Validate
 */
class Validate
{
    /**
     * Checks If Given Object Is Loaded And Exists.
     *
     * @param object $object
     *
     * @return bool
     */
    public function isLoadedObject($object)
    {
        return \Validate::isLoadedObject($object);
    }

    /**
     * Checks If Given Float Is Unsigned.
     *
     * @param float $value
     *
     * @return bool
     */
    public function isUnsignedFloat($value)
    {
        return \Validate::isUnsignedFloat($value);
    }
}
