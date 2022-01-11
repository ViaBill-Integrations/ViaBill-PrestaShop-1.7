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

namespace ViaBill\Builder\Template;

/**
 * Interface TemplateInterface
 */
interface TemplateInterface
{
    /**
     * Set Smarty Method Interface.
     *
     * @param \Smarty $smarty
     */
    public function setSmarty(\Smarty $smarty);

    /**
     * Set Smarty Params Method Interface.
     *
     * @return array
     */
    public function getSmartyParams();

    /**
     * Get HTML Method Interface.
     *
     * @return string
     */
    public function getHtml();
}
