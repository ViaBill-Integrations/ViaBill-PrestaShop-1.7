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
 * Class DynamicPriceTemplate
 */
class DynamicPriceTemplate implements TemplateInterface
{
    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Price Variable Declaration.
     *
     * @var float
     */
    private $price;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * DynamicPriceTemplate constructor.
     *
     * @param \ViaBill $module
     */
    public function __construct(\ViaBill $module)
    {
        $this->module = $module;
    }

    /**
     * Sets Smarty From Given Param.
     *
     * @param \Smarty $smarty
     */
    public function setSmarty(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Sets Price From Given Param.
     *
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return [
            'dynamicPrice' => $this->price,
        ];
    }

    /**
     * Gets Smarty Dynamic Price Tag Holder HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());

        return $this->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/front/dynamic-price-tag-holder.tpl'
        );
    }
}
