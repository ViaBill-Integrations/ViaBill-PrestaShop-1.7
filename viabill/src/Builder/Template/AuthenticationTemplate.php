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
 * Class AuthenticationTemplate
 */
class AuthenticationTemplate implements TemplateInterface
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * AuthenticationTemplate constructor.
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
     * Sets Smarty From Given Param.
     *
     * @param \Smarty $smarty
     */
    public function setSmarty(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * New User Variable Declaration.
     *
     * @var string
     */
    private $newUser;

    /**
     * Sets New User From Given Param.
     *
     * @param string $newUser
     */
    public function setNewUser($newUser)
    {
        $this->newUser = $newUser;
    }

    /**
     * Existing User Variable Declaration.
     *
     * @var string
     */
    private $existingUser;

    /**
     * Sets Existing User From Given Param.
     *
     * @param string $existingUser
     */
    public function setExistingUser($existingUser)
    {
        $this->existingUser = $existingUser;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams()
    {
        return [
            'newUser' => $this->newUser,
            'existingUser' => $this->existingUser,
        ];
    }

    /**
     * Gets Smarty Authentication HTML Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml()
    {
        $this->smarty->assign($this->getSmartyParams());

        return $this->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/authentication.tpl');
    }
}
