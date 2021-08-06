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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 *
 * @param Viabill $module
 * @return bool
 */
function upgrade_module_1_1_16(Viabill $module)
{
    $db = Db::getInstance();

    # insert the tabs

    $query = 'SELECT `id_parent` FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminViaBillSettings"';
    $parent_id = $db->getValue($query);
    if (empty($parent_id)) {
        // sanity check
        return true;
    }
    
    // Check if the columns 'enabled' is present or not
	$enabled_column_name = '';
	$enabled_column_value = '';
	
    $query = 'SELECT * FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminViaBillSettings"';
	$parent_row = $db->getRow($query);   
    if (!empty($parent_row)) {
        if (isset($parent_row['enabled'])) {
			$enabled_column_name = '`enabled`, ';
			$enabled_column_value = '1, ';
		}
    }
    
    $query = 'INSERT INTO `'._DB_PREFIX_.'tab`
        (`id_parent`, `position`, `module`, `class_name`, `active`, '.$enabled_column_name.'`hide_host_mode`) VALUES '.
        '('.$parent_id.', 4, "viabill", "AdminViaBillContact", 1, '.$enabled_column_value.'0)';
    $db->execute($query);
    $id = $db->insert_id();
    $count = (int) $db->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'lang');
    while ($count > 0) {
        $db->execute(
            'INSERT INTO `'._DB_PREFIX_.'tab_lang`
                    (`id_tab`, `id_lang`, `name`)
                    VALUES ('.$id.', '. $count.', "Contact")'
        );
        $count--;
    }

    $query = 'INSERT INTO `'._DB_PREFIX_.'tab`
        (`id_parent`, `position`, `module`, `class_name`, `active`, '.$enabled_column_name.'`hide_host_mode`) VALUES '.
        '('.$parent_id.', 5, "viabill", "AdminViaBillTroubleshoot", 1, '.$enabled_column_value.'0)';
    $db->execute($query);
    $id = $db->insert_id();
    $count = (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'lang');
    while ($count > 0) {
        $db->execute(
            'INSERT INTO `'._DB_PREFIX_.'tab_lang`
                    (`id_tab`, `id_lang`, `name`)
                    VALUES ('.$id.', '.  $count.', "Troubleshooting")'
        );
        $count--;
    }

    $query = 'INSERT INTO `'._DB_PREFIX_.'authorization_role` (`slug`) VALUES '.
        '("ROLE_MOD_TAB_ADMINVIABILLCONTACT_CREATE"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLCONTACT_READ"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLCONTACT_UPDATE"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLCONTACT_DELETE"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_CREATE"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_READ"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_UPDATE"),'.
        '("ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_DELETE")';
    $result = $db->execute($query);

    if ($result) {
        $query = 'INSERT INTO `'._DB_PREFIX_.'access` (`id_profile`, `id_authorization_role`) '.
            'SELECT "1", `id_authorization_role` FROM `'
            ._DB_PREFIX_.'authorization_role` WHERE `slug` IN ('.
            '"ROLE_MOD_TAB_ADMINVIABILLCONTACT_CREATE",'.
            '"ROLE_MOD_TAB_ADMINVIABILLCONTACT_DELETE",'.
            '"ROLE_MOD_TAB_ADMINVIABILLCONTACT_READ",'.
            '"ROLE_MOD_TAB_ADMINVIABILLCONTACT_UPDATE",'.
            '"ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_CREATE",'.
            '"ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_READ",'.
            '"ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_UPDATE",'.
            '"ROLE_MOD_TAB_ADMINVIABILLTROUBLESHOOT_DELETE")';

        $db->execute($query);
    }
    
    return true;
}
