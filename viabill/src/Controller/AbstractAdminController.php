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

namespace ViaBill\Controller;

use ViaBill\Config\Config;
use ViaBill\Install\Tab;

/**
 * Class AbstractAdminController
 */
class AbstractAdminController extends \ModuleAdminController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    public $module;

    /**
     * AbstractAdminController constructor.
     *
     * @throws \PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * Calls Redirect To Login Method.
     */
    public function init()
    {
        $this->redirectToLogin();
        parent::init();
    }

    /**
     * Redirects To Authentication Tab If User Is Not Loggen In.
     *
     * @throws \Exception
     */
    private function redirectToLogin()
    {
        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        if ($this instanceof \AdminViaBillAuthenticationController || $config->isLoggedIn()) {
            return;
        }

        /**
         * @var Tab $tab
         */
        $tab = $this->module->getModuleContainer()->get('tab');

        \Tools::redirectAdmin($this->context->link->getAdminLink($tab->getControllerAuthenticationName()));
    }

    /**
     * Gets Info Block Template.
     *
     * @param $infoBlockText
     *
     * @return string
     *
     * @throws \SmartyException
     */
    protected function getInfoBlockTemplate($infoBlockText)
    {
        /**
         * @var \ViaBill\Builder\Template\InfoBlockTemplate $infoBlockTemplate
         */
        $infoBlockTemplate = $this->module->getModuleContainer()->get('builder.template.infoBlock');
        $infoBlockTemplate->setSmarty($this->context->smarty);
        $infoBlockTemplate->setInfoBlockText($infoBlockText);

        return $infoBlockTemplate->getHtml();
    }
}
