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

namespace ViaBill\Builder\Template;

/**
 * Class DynamicPriceTemplate
 *
 * @package ViaBill\Builder\Template
 */
class InfoBlockTemplate implements TemplateInterface
{
    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Info Block Text Variable Declaration.
     *
     * @var string
     */
    private $infoBlockText;

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
     * Sets Info Block Text From Given Param.
     *
     * @param string $infoBlockText
     */
    public function setInfoBlockText($infoBlockText)
    {
        $this->infoBlockText = $infoBlockText;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return array(
            'infoBlockText' => $this->infoBlockText
        );
    }

    /**
     * Gets Smarty Info Block HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());
        return $this->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/info-block.tpl'
        );
    }
}
