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
function upgrade_module_1_1_11($module)
{
    return $module->registerHook('actionOrderHistoryAddAfter') &&
        $module->unregisterHook('actionOrderStatusPostUpdate');
}
