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

use ViaBill\Config\Config;  

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 *
 * @param Viabill $module
 * @return bool
 */
function upgrade_module_1_1_18($module)
{
    $db = Db::getInstance();
    
    $sql = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'viabill_order_conf_mail` (
            `id_viabill_order_conf_mail`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `order_id` INT(64) NOT NULL,
            `lang_id` INT(32) NOT NULL,
            `subject` varchar(512) NOT NULL,
            `template_vars` text NOT NULL,
            `date_created` datetime NOT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ';      

    if ($db->execute($sql) == false) {
        return false;
    }

    $orderStateAccepted = Configuration::get(Config::PAYMENT_ACCEPTED);

    $sql = '
        UPDATE `' . _DB_PREFIX_ . 'order_state` SET `send_email`= 1 WHERE `id_order_state` ='
            . (int) $orderStateAccepted;

    if ($db->execute($sql) == false) {
        return false;
    }

    $sql = '
        UPDATE `' . _DB_PREFIX_ . 'order_state_lang` SET `template` = "order_conf" WHERE `id_order_state` = ' 
        . (int) $orderStateAccepted;

    if ($db->execute($sql) == false) {
        return false;
    }    

    return true;
}