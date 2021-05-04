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
 * Class TagScriptTemplate
 *
 * @package ViaBill\Builder\Template
 */
class TagScriptTemplate implements TemplateInterface
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * TagScriptTemplate constructor.
     *
     * @param \ViaBill $module
     */
    public function __construct(\ViaBill $module)
    {
        $this->module = $module;
    }

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * Tag Script Variable Declaration.
     *
     * @var string
     */
    private $tagScript;

    /**
     * Sets Tag Script From Given Param.
     *
     * @param string $tagScript
     */
    public function setTagScript($tagScript)
    {
        $this->tagScript = $tagScript;
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
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return array(
            'tagScript' => $this->tagScript
        );
    }

    /**
     * Gets Smarty Tag Script HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());
        return $this->smarty->fetch($this->module->getLocalPath().'views/templates/front/tag-script.tpl');
    }
}
