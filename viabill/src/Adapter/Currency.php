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
 * Class Currency
 *
 * @package ViaBill\Adapter
 */
class Currency
{
    /**
     * Gets Currency ID By Iso Code.
     *
     * @param string $isoCode
     *
     * @return int
     */
    public function getIdByIsoCode($isoCode)
    {
        return \Currency::getIdByIsoCode($isoCode);
    }
}
