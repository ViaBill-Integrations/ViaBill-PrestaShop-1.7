<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
 * @copyright Copyright (c) Viabill
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Builder\Template;

use ViaBill\Config\Config;

/**
 * Class TermsAndConditionsTemplate
 *
 * @package ViaBill\Builder\Template
 */
class TermsAndConditionsTemplate implements TemplateInterface
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Terms And Conditions Link Country Variable Declaration.
     *
     * @var $termsLinkCountry
     */
    private $termsLinkCountry;

    /**
     * TermsAndConditionsTemplate constructor.
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
     * Sets Country For Terms And Conditions Link.
     *
     * @param $termsLinkCountry
     */
    public function setTermsLinkCountry($termsLinkCountry)
    {
        $this->termsLinkCountry = $termsLinkCountry;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return array(
            'termsLink' => Config::TERMS_AND_CONDITIONS_LINK,
            'termsLinkCountry' => $this->termsLinkCountry,
        );
    }

    /**
     * Gets Smarty Terms And Conditions HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());
        return $this->smarty->fetch($this->module->getLocalPath().'views/templates/admin/terms-and-conditions.tpl');
    }
}
