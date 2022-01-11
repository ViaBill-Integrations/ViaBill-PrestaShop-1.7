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

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestFactory
 */
class RequestFactory
{
    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return Request
     */
    public function create()
    {
        return Request::createFromGlobals();
    }
}
