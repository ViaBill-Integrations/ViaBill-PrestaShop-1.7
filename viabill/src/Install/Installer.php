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
     * @return bool
     *
     * @throws Exception
     */
    public function install()
    {
        if (!$this->registerHooks() ||
            !$this->registerConfiguration() ||
            !$this->installDb() ||
            !$this->installPaymentStatuses()
        ) {
            return false;
        }

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

            try {
                $this->execute($database, $sqlStatements);
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage());
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
