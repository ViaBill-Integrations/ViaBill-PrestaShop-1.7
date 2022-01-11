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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Viabill $module
 *
 * @return bool
 */
function upgrade_module_1_1_15(Viabill $module)
{
    $module->registerHook('displayAdminOrderTabContent');
    $module->registerHook('actionOrderGridDefinitionModifier');

    return true;
}
