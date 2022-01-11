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
 * Class Context
 */
class Context
{
    /**
     * Gets Link From Context.
     *
     * @return \Link
     */
    public function getLink()
    {
        return \Context::getContext()->link;
    }

    /**
     * Gets Context.
     *
     * @return \Context
     */
    public function getContext()
    {
        return \Context::getContext();
    }
}
