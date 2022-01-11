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

namespace ViaBill\Util;

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ViaBill\Config\Config;

class DebugLog
{
    const LOG_FILEPATH = '/var/log/debug.log';

    public static function msg($msg, $level = 'debug')
    {
        if (!self::isEnabled()) {
            return;
        }

        $logger = new Logger('ViabillLogger');

        $fileName = self::getFilename();
        $logger->pushHandler(new StreamHandler($fileName));
        $logger->pushHandler(new RotatingFileHandler($fileName));
        $logger->pushHandler(new FirePHPHandler());

        switch ($level) {
            case 'error':
                $logger->error($msg);
                break;
            case 'debug':
                $logger->debug($msg);
                break;
            default:
                $logger->notice($msg);
                break;
        }

        $logger->close();
    }

    public static function isEnabled()
    {
        $isEnabled = (bool) \Configuration::get(Config::ENABLE_DEBUG);

        return $isEnabled;
    }

    public static function getFilename()
    {
        $full_path = _PS_ROOT_DIR_ . '/modules/viabill' . self::LOG_FILEPATH;
        if (file_exists($full_path)) {
            $full_path = realpath($full_path);
        }

        return $full_path;
    }
}
