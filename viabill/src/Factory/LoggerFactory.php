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

namespace ViaBill\Factory;

use ViaBill\Config\Config;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LoggerFactory
 *
 * @package ViaBill\Factory
 */
class LoggerFactory
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Config Variable Declaration.
     *
     * @var Config
     */
    private $config;

    /**
     * LoggerFactory constructor.
     *
     * @param \ViaBill $module
     * @param Config $config
     */
    public function __construct(\ViaBill $module, Config $config)
    {
        $this->module = $module;
        $this->config = $config;
    }

    /**
     * Creates New Logger Instance.
     *
     * @return Logger
     */
    public function create()
    {
        $logger = new Logger($this->module->name);

        $isTestEnv = $this->config->isTestingEnvironment();

        $isDebugLogEnabled = $this->config->isDebug();

        $fileName = $this->module->getLocalPath().'var/log/'.$this->module->name.'.log';

        $logger->pushHandler(new StreamHandler($fileName));
        $logger->pushHandler(new RotatingFileHandler($fileName));
        $logger->pushHandler(new FirePHPHandler());

        return $logger;
    }
}
