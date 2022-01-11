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

namespace ViaBill\Service;

use ViaBill\Adapter\Configuration;
use ViaBill\Config\Config;
use ViaBill\Object\ViaBillUser;

/**
 * Class UserService
 */
class UserService
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'UserService';

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * User Service Config Variable Declaration.
     *
     * @var Config
     */
    private $config;

    /**
     * User Service Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * UserService constructor.
     *
     * @param \ViaBill $module
     * @param Config $config
     * @param Configuration $configuration
     */
    public function __construct(
        \ViaBill $module,
        Config $config,
        Configuration $configuration
    ) {
        $this->module = $module;
        $this->config = $config;
        $this->configuration = $configuration;
    }

    /**
     *  Checks If User Is Logged In And Gets User.
     *
     * @return ViaBillUser
     */
    public function getUser()
    {
        if (!$this->config->isLoggedIn()) {
            return new ViaBillUser('', '', '');
        }

        return new ViaBillUser(
            $this->configuration->get(Config::API_KEY),
            $this->configuration->get(Config::API_SECRET),
            $this->configuration->get(Config::API_TAGS_SCRIPT)
        );
    }
}
