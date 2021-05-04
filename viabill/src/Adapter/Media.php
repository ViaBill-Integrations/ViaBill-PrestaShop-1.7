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

use Context;

/**
 * Class Media
 *
 * @package ViaBill\Adapter
 */
class Media
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Media constructor.
     *
     * @param \ViaBill $module
     */
    public function __construct(\ViaBill $module)
    {
        $this->module = $module;
    }

    /**
     * Adds Front Office CSS.
     *
     * @param Context $context
     * @param string $url
     * @param array $params
     */
    public function addCss(Context $context, $url, $params = array())
    {
        $context->controller->registerStylesheet(
            'modules/'.$this->module->name.'/'.$url,
            'modules/'.$this->module->name.'/'.$url,
            $params
        );
    }

    /**
     * Adds Front Office JS.
     *
     * @param Context $context
     * @param string $url
     * @param array $params
     */
    public function addJs(Context $context, $url, $params = array())
    {
        $context->controller->registerJavascript(
            'modules/'.$this->module->name.'/'.$url,
            'modules/'.$this->module->name.'/'.$url,
            $params
        );
    }

    /**
     * Adds JS Variables Definitions.
     *
     * @param array $data
     */
    public function addJsDef(array $data)
    {
        \Media::addJsDef($data);
    }

    /**
     * Adds Back Office JS.
     *
     * @param Context $context
     * @param string $fileUrl
     */
    public function addJsAdmin(Context $context, $fileUrl)
    {
        $context->controller->addJS(
            $this->module->getPathUri().'views/js/admin/'.$fileUrl
        );
    }

    /**
     * Adds Back Office CSS.
     *
     * @param Context $context
     * @param string $fileUrl
     */
    public function addCssAdmin(Context $context, $fileUrl)
    {
        $context->controller->addCSS(
            $this->module->getPathUri().'views/css/admin/'.$fileUrl
        );
    }
}
