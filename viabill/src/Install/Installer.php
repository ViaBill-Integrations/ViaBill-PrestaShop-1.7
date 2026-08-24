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

use Exception;
use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;

/**
 * Class Installer
 */
class Installer extends AbstractInstaller
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'Installer';

    /**
     * All back-office controller class names ever registered by any version
     * of this module. Used by the self-healing tab cleanup.
     *
     * Note: 'AdminViabillConflict' (lowercase "b") is included on purpose -
     * older releases registered/linked the Conflict controller with
     * inconsistent casing.
     *
     * @var array
     */
    private static $tabClassNames = [
        'AdminViaBillTabs',
        'AdminViaBillActions',
        'AdminViaBillAuthentication',
        'AdminViaBillSettings',
        'AdminViaBillCustomCode',
        'AdminViaBillContact',
        'AdminViaBillTroubleshoot',
        'AdminViaBillConflict',
        'AdminViabillConflict',
    ];

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
     * Installer constructor.
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
     * Calls Installation Methods.
     *
     * PATCH: before anything else, remove any leftover back-office tab rows
     * (and their lang / authorization-role / access rows) from previous
     * installations. PrestaShop's ModuleTabRegister silently SKIPS class
     * names that already exist in the ps_tab table, so a broken leftover row
     * (empty `module` column, orphaned id_parent, duplicate, inactive) was
     * never repaired by a reinstall and produced "Page not found" when
     * opening AdminViaBillSettings. After the cleanup, the tabs are created
     * explicitly with known-good values, so the module works even when it is
     * installed through a code path where ModuleTabRegister never runs
     * (CLI, custom deployment scripts). ModuleTabRegister will then skip
     * them as "already registered", which is exactly what we want.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function install()
    {
        static::cleanLeftoverTabs();

        if (!$this->registerHooks() ||
            !$this->registerConfiguration() ||
            !$this->installDb() ||
            !$this->installPaymentStatuses()
        ) {
            return false;
        }

        $this->installTabs();

        return true;
    }

    /**
     * Removes every ps_tab / ps_tab_lang / authorization_role / access row
     * that belongs to any (past or present) ViaBill back-office controller.
     *
     * Public and static so it can also be called by the UnInstaller and by
     * upgrade scripts. Never throws - a failed cleanup must not break the
     * install/uninstall process.
     */
    public static function cleanLeftoverTabs()
    {
        try {
            $db = \Db::getInstance();

            $classNamesIn = implode(
                ', ',
                array_map(
                    function ($className) {
                        return '"' . pSQL($className) . '"';
                    },
                    static::$tabClassNames
                )
            );

            $tabIds = $db->executeS(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` IN (' . $classNamesIn . ')'
            );

            if (!empty($tabIds)) {
                foreach ($tabIds as $row) {
                    $idTab = (int) $row['id_tab'];

                    if (!$idTab) {
                        continue;
                    }

                    // Prefer the ObjectModel delete: it also removes lang
                    // rows, authorization roles and access rows when the tab
                    // data is sane.
                    $deleted = false;
                    try {
                        $tab = new \Tab($idTab);
                        if (\Validate::isLoadedObject($tab)) {
                            $deleted = $tab->delete();
                        }
                    } catch (Exception $exception) {
                        $deleted = false;
                    }

                    // Fallback: raw removal for corrupt rows that the
                    // ObjectModel refuses to handle.
                    if (!$deleted) {
                        $db->execute(
                            'DELETE FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_tab` = ' . $idTab
                        );
                        $db->execute(
                            'DELETE FROM `' . _DB_PREFIX_ . 'tab_lang` WHERE `id_tab` = ' . $idTab
                        );
                    }
                }
            }

            // Sweep orphaned authorization roles / access rows created by old
            // upgrade scripts (raw INSERTs that bypassed the Tab object).
            $roleSlugPattern = 'ROLE_MOD_TAB_ADMINVIABILL%';

            $db->execute(
                'DELETE a FROM `' . _DB_PREFIX_ . 'access` a
                    INNER JOIN `' . _DB_PREFIX_ . 'authorization_role` r
                        ON r.`id_authorization_role` = a.`id_authorization_role`
                    WHERE r.`slug` LIKE "' . pSQL($roleSlugPattern) . '"'
            );

            $db->execute(
                'DELETE FROM `' . _DB_PREFIX_ . 'authorization_role`
                    WHERE `slug` LIKE "' . pSQL($roleSlugPattern) . '"'
            );

            // Also remove any module_access_criterion-style leftovers on shops
            // that have the ps_module_access table (older 1.6 migrations);
            // ignore failures silently as the table may not exist.
            try {
                $db->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'tab_module_preference`
                        WHERE `module` = "viabill"'
                );
            } catch (Exception $exception) {
                // Table may not exist on this PrestaShop version - ignore.
            }

            // Invalidate PrestaShop's in-memory / persistent tab caches so
            // Tab::getIdFromClassName() does not keep serving stale ids
            // during this request.
            if (method_exists('\Tab', 'resetStaticCache')) {
                \Tab::resetStaticCache();
            }

            \Cache::clean('*');
        } catch (Exception $exception) {
            \PrestaShopLogger::addLog(
                'ViaBill tab cleanup failed: ' . $exception->getMessage(),
                3
            );
        }
    }

    /**
     * Explicitly creates the module's back-office tabs with known-good
     * values (correct `module` column, correct parent ids, all languages).
     *
     * Runs after cleanLeftoverTabs(), so no duplicates can exist. When the
     * module is installed through the standard module manager,
     * ModuleTabRegister will afterwards detect these class names as already
     * registered and skip them.
     *
     * Never throws - tab creation problems are logged, and ModuleTabRegister
     * still acts as a second chance on standard installations.
     */
    private function installTabs()
    {
        try {
            $languages = \Language::getLanguages(false);

            $parentModulesId = $this->getTabIdByClassNames(
                ['AdminParentModulesSf', 'AdminParentModules']
            );

            $definitions = [
                [
                    'class_name' => 'AdminViaBillTabs',
                    'name' => 'ViaBill',
                    'visible' => false,
                    'id_parent' => $parentModulesId,
                ],
                [
                    'class_name' => 'AdminViaBillActions',
                    'name' => 'ViaBill Ajax',
                    'visible' => false,
                    'id_parent' => $parentModulesId,
                ],
                [
                    'class_name' => 'AdminViaBillAuthentication',
                    'name' => 'ViaBill Authentication',
                    'visible' => true,
                    'id_parent' => null, // resolved to AdminViaBillTabs below
                ],
                [
                    'class_name' => 'AdminViaBillSettings',
                    'name' => 'ViaBill Settings',
                    'visible' => true,
                    'id_parent' => null,
                ],
                [
                    'class_name' => 'AdminViaBillCustomCode',
                    'name' => 'ViaBill Custom CSS/JS',
                    'visible' => true,
                    'id_parent' => null,
                ],
                [
                    'class_name' => 'AdminViaBillContact',
                    'name' => 'ViaBill Contact',
                    'visible' => true,
                    'id_parent' => null,
                ],
                [
                    'class_name' => 'AdminViaBillTroubleshoot',
                    'name' => 'ViaBill Troubleshooting',
                    'visible' => true,
                    'id_parent' => null,
                ],
                [
                    // Not part of the visible menu, but the Settings page
                    // links to this controller, so it must be dispatchable.
                    'class_name' => 'AdminViaBillConflict',
                    'name' => 'ViaBill Conflict',
                    'visible' => false,
                    'id_parent' => null,
                ],
            ];

            $viaBillRootTabId = 0;

            foreach ($definitions as $definition) {
                $idParent = $definition['id_parent'];

                if (null === $idParent) {
                    $idParent = $viaBillRootTabId;
                }

                $idTab = $this->createTab(
                    $definition['class_name'],
                    $definition['name'],
                    (bool) $definition['visible'],
                    (int) $idParent,
                    $languages
                );

                if ('AdminViaBillTabs' === $definition['class_name']) {
                    $viaBillRootTabId = (int) $idTab;
                }
            }

            if (method_exists('\Tab', 'resetStaticCache')) {
                \Tab::resetStaticCache();
            }
        } catch (Exception $exception) {
            \PrestaShopLogger::addLog(
                'ViaBill tab installation failed: ' . $exception->getMessage(),
                3
            );
        }
    }

    /**
     * Creates a single back-office tab, unless it already exists.
     *
     * @param string $className
     * @param string $name
     * @param bool $visible
     * @param int $idParent
     * @param array $languages
     *
     * @return int the tab id (existing or newly created), 0 on failure
     */
    private function createTab($className, $name, $visible, $idParent, array $languages)
    {
        try {
            $existingId = (int) \Tab::getIdFromClassName($className);
            if ($existingId > 0) {
                return $existingId;
            }

            $tab = new \Tab();
            $tab->class_name = $className;
            $tab->module = $this->module->name;
            $tab->id_parent = (int) $idParent;
            $tab->active = $visible;

            // PrestaShop 1.7.7+ / 8.x / 9.x have an `enabled` flag that must
            // be true for the controller to be reachable.
            if (property_exists($tab, 'enabled')) {
                $tab->enabled = true;
            }

            foreach ($languages as $language) {
                $tab->name[(int) $language['id_lang']] = $name;
            }

            if (!$tab->add()) {
                \PrestaShopLogger::addLog(
                    'ViaBill could not create back-office tab ' . $className,
                    3
                );

                return 0;
            }

            return (int) $tab->id;
        } catch (Exception $exception) {
            \PrestaShopLogger::addLog(
                'ViaBill could not create back-office tab ' . $className .
                ': ' . $exception->getMessage(),
                3
            );

            return 0;
        }
    }

    /**
     * Returns the id of the first existing tab among the given class names.
     *
     * @param array $classNames
     *
     * @return int 0 when none of them exists (tab becomes a hidden root)
     */
    private function getTabIdByClassNames(array $classNames)
    {
        foreach ($classNames as $className) {
            $idTab = (int) \Tab::getIdFromClassName($className);

            if ($idTab > 0) {
                return $idTab;
            }
        }

        return 0;
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
     * Registers Module Hooks.
     *
     * @return bool
     */
    private function registerHooks()
    {
        $hooks = $this->moduleConfiguration['hooks'];

        if (empty($hooks)) {
            return true;
        }

        foreach ($hooks as $hook) {
            if (!$this->module->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registers Module Configuration.
     *
     * @return bool
     */
    private function registerConfiguration()
    {
        $configuration = $this->moduleConfiguration['configuration'];

        if (empty($configuration)) {
            return true;
        }

        foreach ($configuration as $configName => $value) {
            if (!\Configuration::updateValue($configName, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Installs Payment Statuses.
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function installPaymentStatuses()
    {
        \Db::getInstance()->execute('START TRANSACTION;');

        $orderStatuses = Config::getOrderStatuses();
        $languages = \Language::getLanguages();

        $imagePath = $this->module->getLocalPath() . 'views/img/';
        $images = [];
        foreach ($orderStatuses as $stateConfig) {
            $orderState = new \OrderState();
            $orderState->module_name = $this->module->name;
            $orderState->unremovable = true;

            $configName = '';
            $imagePathFull = '';
            switch ($stateConfig) {
                case Config::PAYMENT_PENDING:
                    $orderState->color = '#4169E1';
                    $orderState->send_email = false;
                    $this->fillMultiLangName(
                        $orderState,
                        $languages,
                        $this->module->l('Payment pending by ViaBill', self::FILENAME)
                    );
                    $imagePathFull = $imagePath . 'accept.gif';
                    $configName = Config::PAYMENT_PENDING;
                    break;
                case Config::PAYMENT_ACCEPTED:
                    $orderState->color = '#4169E1';
                    $orderState->paid = true;
                    $orderState->send_email = true;
                    $orderState->logable = true;
                    $this->fillMultiLangName(
                        $orderState,
                        $languages,
                        $this->module->l('Payment accepted by ViaBill', self::FILENAME)
                    );
                    $this->fillMultiLangTemplate(
                        $orderState,
                        $languages,
                        'order_conf'
                    );
                    $imagePathFull = $imagePath . 'accept.gif';
                    $configName = Config::PAYMENT_ACCEPTED;
                    break;
                case Config::PAYMENT_COMPLETED:
                    $orderState->color = '#32CD32';
                    $orderState->paid = true;
                    $orderState->logable = true;
                    $orderState->invoice = true;
                    $this->fillMultiLangName(
                        $orderState,
                        $languages,
                        $this->module->l('Payment completed by ViaBill', self::FILENAME)
                    );
                    $imagePathFull = $imagePath . 'complete.gif';
                    $configName = Config::PAYMENT_COMPLETED;
                    break;
                case Config::PAYMENT_REFUNDED:
                    $orderState->color = '#ec2e15';
                    $this->fillMultiLangName(
                        $orderState,
                        $languages,
                        $this->module->l('Payment refunded by ViaBill', self::FILENAME)
                    );
                    $imagePathFull = $imagePath . 'refund.gif';
                    $configName = Config::PAYMENT_REFUNDED;
                    break;
                case Config::PAYMENT_CANCELED:
                    $orderState->color = '#DC143C';
                    $orderState->send_email = false;
                    $this->fillMultiLangName(
                        $orderState,
                        $languages,
                        $this->module->l('Payment canceled by ViaBill', self::FILENAME)
                    );
                    $imagePathFull = $imagePath . 'cancel.gif';
                    $configName = Config::PAYMENT_CANCELED;
                    break;
            }

            if (!$orderState->save()) {
                \Db::getInstance()->execute('ROLLBACK;');

                return false;
            }

            $images[] = [
                'name' => 'order_state_mini_' . $orderState->id,
                'id_state' => $orderState->id,
                'path' => $imagePathFull,
            ];
            \Configuration::updateValue($configName, $orderState->id);
        }

        \Db::getInstance()->execute('COMMIT;');
        $this->uploadOrderStateImages($images);

        return true;
    }

    /**
     * Fills Multi Language Order State Names
     *
     * @param \OrderState $orderState
     * @param array $languages
     * @param string $name
     */
    private function fillMultiLangName(\OrderState $orderState, array $languages, $name)
    {
        foreach ($languages as $language) {
            $orderState->name[$language['id_lang']] = $name;
        }
    }

    /**
     * Fills Multi Language Order State Template
     *
     * @param \OrderState $orderState
     * @param array $languages
     * @param string $name
     */
    private function fillMultiLangTemplate(\OrderState $orderState, array $languages, $name)
    {
        foreach ($languages as $language) {
            $orderState->template[$language['id_lang']] = $name;
        }
    }

    /**
     * Installs Module Database Tables.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function installDb()
    {
        $installSqlFiles = glob($this->module->getLocalPath() . 'sql/install/*.sql');

        if (empty($installSqlFiles)) {
            return true;
        }

        $database = \Db::getInstance();

        foreach ($installSqlFiles as $sqlFile) {
            $sqlStatements = $this->getSqlStatements($sqlFile);

            // Split the string into an array of individual SQL statements
            $statementsArray = explode(';', $sqlStatements);

            // Removing any empty elements from the array, in case there's a trailing semicolon
            $statementsArray = array_filter($statementsArray);

            foreach ($statementsArray as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }

                try {
                    $this->execute($database, $statement);
                } catch (Exception $exception) {
                    throw new Exception($exception->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * Uploads Order State Images.
     *
     * @param array $images
     */
    private function uploadOrderStateImages(array $images)
    {
        $shopIds = \Shop::getShops(false, null, true);
        $imageSize = 16;

        foreach ($images as $image) {
            $destination = _PS_ORDER_STATE_IMG_DIR_ . $image['id_state'] . '.gif';
            \Tools::copy($image['path'], $destination);
        }

        foreach ($shopIds as $idShop) {
            foreach ($images as $image) {
                $fullName = $image['name'] . '_' . $idShop . '.gif';
                \ImageManager::thumbnail(
                    $image['path'],
                    $fullName,
                    $imageSize,
                    'gif'
                );
            }
        }
    }
}
