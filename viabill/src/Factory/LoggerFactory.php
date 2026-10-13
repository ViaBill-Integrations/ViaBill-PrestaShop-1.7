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

namespace ViaBill\Factory;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ViaBill\Config\Config;
use ViaBill\Util\DebugLog;

/**
 * Class LoggerFactory
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

        // Outside the web accessible module directory (see DebugLog::getLogDir()).
        $fileName = DebugLog::getLogDir() . $this->module->name . '.log';

        $logger->pushHandler(new StreamHandler($fileName));
        $logger->pushHandler(new RotatingFileHandler($fileName));

        return $logger;
    }
}
