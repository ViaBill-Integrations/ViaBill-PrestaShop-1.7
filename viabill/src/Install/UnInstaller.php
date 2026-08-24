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

namespace ViaBill\Install;

use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;

/**
 * Class UnInstaller
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
     * PATCH: explicitly remove all ViaBill back-office tab rows (plus their
     * lang / authorization-role / access rows).
     *
     * PrestaShop's core Module::uninstall() only deletes tabs whose `module`
     * column is exactly this module's name. Rows created by old versions
     * with an empty or different `module` value (e.g. the raw SQL inserts in
     * the historical upgrade scripts) survived every uninstall, and because
     * ModuleTabRegister skips class names that already exist, they were also
     * never repaired on reinstall - which is what produced the
     * "Page not found" on AdminViaBillSettings for shops with leftovers.
     *
     * The sweep is delegated to Installer::cleanLeftoverTabs() so install
     * and uninstall share the exact same cleanup logic.
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
        $this->removeTabs();

        return true;
    }

    /**
     * Removes every ViaBill back-office tab, past or present, regardless of
     * the value of its `module` column. Never throws.
     */
    private function removeTabs()
    {
        try {
            Installer::cleanLeftoverTabs();
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog(
                'ViaBill tab removal on uninstall failed: ' . $exception->getMessage(),
                3
            );
        }
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
        $uninstallSqlFileName = $this->module->getLocalPath() . 'sql/uninstall/uninstall.sql';
        if (!file_exists($uninstallSqlFileName)) {
            return true;
        }

        $database = \Db::getInstance();

        $sqlStatements = $this->getSqlStatements($uninstallSqlFileName);

        // Split the string into an array of individual SQL statements
        $statementsArray = explode(';', $sqlStatements);

        // Removing any empty elements from the array, in case there's a trailing semicolon
        $statementsArray = array_filter($statementsArray);

        $success = true;

        foreach ($statementsArray as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }

            $success = $this->execute($database, $statement);
            if (!$success) {
                break;
            }
        }

        return $success;
    }
}
