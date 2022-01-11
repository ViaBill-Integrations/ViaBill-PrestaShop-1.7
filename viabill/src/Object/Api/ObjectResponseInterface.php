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

namespace ViaBill\Object\Api;

/**
 * Interface ObjectResponseInterface
 */
interface ObjectResponseInterface
{
    /**
     * Has Errors Interface.
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Get Errors Interface.
     *
     * @return ApiResponseError[]
     */
    public function getErrors();
}
