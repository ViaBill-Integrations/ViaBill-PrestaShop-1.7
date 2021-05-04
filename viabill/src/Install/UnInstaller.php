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

namespace ViaBill\Install;

use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;

/**
 * Class UnInstaller
 *
 * @package ViaBill\Install
 */
class UnInstaller extends AbstractInstaller
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Module Configuration Variable Declaration.
     *
     * @var array
     */
    private $moduleConfiguration;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * UnInstaller constructor.
     *
     * @param \ViaBill $module
     * @param array $moduleConfiguration
     * @param Tools $tools
     */
    public function __construct(
        \ViaBill $module,
        array $moduleConfiguration,
        Tools $tools
    ) {
        $this->module = $module;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->tools = $tools;
    }

    /**
     * Calls Uninstall Methods.
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        $this->removeOrderStates();
        $this->removeConfiguration();
        $this->uninstallDb();
        return true;
    }

    /**
     * Gets SQL Statements.
     *
     * @param array $sqlFile
     *
     * @return bool|mixed|string
     */
    protected function getSqlStatements($sqlFile)
    {
        $sqlStatements = $this->tools->fileGetContents($sqlFile);
        $sqlStatements = str_replace('PREFIX_', _DB_PREFIX_, $sqlStatements);
        $sqlStatements = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sqlStatements);
        return $sqlStatements;
    }

    /**
     * Removes Module Configuration.
     */
    private function removeConfiguration()
    {
        $configuration = array_keys($this->moduleConfiguration['configuration']);

        foreach ($configuration as $configName) {
            \Configuration::deleteByName($configName);
        }
    }

    /**
     * Removes Module Order States Configuration.
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function removeOrderStates()
    {
        $orderStates = Config::getOrderStatuses();
        //todo: remove log files on uninstall
        foreach ($orderStates as $configName) {
            $idState = \Configuration::get($configName);
            $state = new \OrderState($idState);

            if (!\Validate::isLoadedObject($state)) {
                continue;
            }

            $state->delete();
        }
    }

    /**
     * Uninstalls Module Database Tables.
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function uninstallDb()
    {
        $uninstallSqlFileName = $this->module->getLocalPath().'sql/uninstall/uninstall.sql';
        if (!file_exists($uninstallSqlFileName)) {
            return true;
        }

        $database = \Db::getInstance();

        $sqlStatements = $this->getSqlStatements($uninstallSqlFileName);
        return (bool) $this->execute($database, $sqlStatements);
    }
}
