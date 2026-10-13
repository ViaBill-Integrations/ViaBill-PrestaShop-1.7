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

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ViaBill\Config\Config;

class DebugLog
{
    /**
     * Debug log file name.
     */
    const LOG_FILENAME = 'debug.log';

    /**
     * Legacy log directory inside the module folder. It is reachable from the
     * web, so log files must no longer be written there.
     */
    const LEGACY_LOG_DIR = 'modules/viabill/var/log/';

    /**
     * PrestaShop's own log directories below the shop root (not web
     * accessible): var/logs on PrestaShop 1.7.4+ and 8.x, app/logs on
     * PrestaShop 1.7.0 - 1.7.3.
     */
    private static $psLogDirs = ['var/logs/', 'app/logs/'];

    /**
     * Module sub-directory inside the PrestaShop log directory.
     */
    const LOG_SUBDIR = 'viabill/';

    /**
     * Keys whose values are never written to the logs.
     */
    const SENSITIVE_KEYS_PATTERN = '/(secret|password|passwd|signature|sha256|checksum|e-?mail|phone|mobile|first_?name|last_?name|full_?name|^name$|address|street|city|postal|zip|tax_?id|customer|custom_?params|secure_?key|token|^key$)/i';

    public static function msg($msg, $level = 'debug')
    {
        if (!self::isEnabled()) {
            return;
        }

        $logger = new Logger('ViabillLogger');

        $fileName = self::getFilename();
        $logger->pushHandler(new StreamHandler($fileName));
        $logger->pushHandler(new RotatingFileHandler($fileName));

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
        return self::getLogDir() . self::LOG_FILENAME;
    }

    /**
     * Gets the directory the module logs are written to, creating and
     * protecting it on first use. Log files left in the old, web accessible
     * module directory are moved there.
     *
     * @return string
     */
    public static function getLogDir()
    {
        static $logDir = null;

        if ($logDir !== null) {
            return $logDir;
        }

        $root = rtrim(_PS_ROOT_DIR_, '/\\') . '/';
        $dir = null;

        foreach (self::$psLogDirs as $psLogDir) {
            // Only use a log directory PrestaShop itself already has.
            if (!is_dir($root . $psLogDir)) {
                continue;
            }

            $candidate = $root . $psLogDir . self::LOG_SUBDIR;
            if (!is_dir($candidate)) {
                @mkdir($candidate, 0755, true);
            }

            if (is_dir($candidate) && is_writable($candidate)) {
                $dir = $candidate;
                break;
            }
        }

        if ($dir === null) {
            // Fall back to the module directory, which is protected by its own
            // deny-all .htaccess on Apache.
            $dir = rtrim(_PS_ROOT_DIR_, '/\\') . '/' . self::LEGACY_LOG_DIR;
            self::protectDirectory($dir);
            $logDir = $dir;

            return $logDir;
        }

        self::protectDirectory($dir);
        self::moveLegacyLogs($dir);

        $logDir = $dir;

        return $logDir;
    }

    /**
     * Returns a log-safe representation of a value: orders are reduced to
     * their id and reference, and personal or secret fields are redacted.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function export($value)
    {
        return var_export(self::sanitize($value), true);
    }

    /**
     * @param mixed $value
     * @param int $depth
     *
     * @return mixed
     */
    private static function sanitize($value, $depth = 0)
    {
        if ($depth > 6) {
            return '[...]';
        }

        if ($value instanceof \Order) {
            return sprintf('Order #%d (%s)', (int) $value->id, $value->reference);
        }

        if ($value instanceof \ObjectModel) {
            return sprintf('%s #%d', get_class($value), (int) $value->id);
        }

        if (is_object($value)) {
            if ($value instanceof \JsonSerializable) {
                $data = $value->jsonSerialize();
            } else {
                $data = [];
                foreach ((array) $value as $key => $item) {
                    // Strip the "\0Class\0" / "\0*\0" prefixes of non-public properties.
                    $key = preg_replace('/^\x00[^\x00]*\x00/', '', (string) $key);
                    $data[$key] = $item;
                }
            }

            return [get_class($value) => self::sanitize($data, $depth + 1)];
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && preg_match(self::SENSITIVE_KEYS_PATTERN, $key)) {
                    $result[$key] = '[redacted]';
                    continue;
                }
                $result[$key] = self::sanitize($item, $depth + 1);
            }

            return $result;
        }

        if (is_string($value)) {
            // Callback URLs carry the callback security key.
            return preg_replace('/([?&]key=)[^&\s\'"]+/i', '$1[redacted]', $value);
        }

        return $value;
    }

    /**
     * Writes a deny-all .htaccess and an index.php into the directory.
     *
     * @param string $dir
     */
    private static function protectDirectory($dir)
    {
        if (!is_dir($dir) || !is_writable($dir)) {
            return;
        }

        $htaccess = $dir . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents(
                $htaccess,
                "# Apache 2.2\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n" .
                "# Apache 2.4\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            );
        }

        $index = $dir . 'index.php';
        if (!file_exists($index)) {
            @file_put_contents(
                $index,
                "<?php\nheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');\n" .
                "header('Cache-Control: no-store, no-cache, must-revalidate');\n" .
                "header('Location: ../');\nexit;\n"
            );
        }
    }

    /**
     * Moves log files out of the old, web accessible module log directory.
     *
     * @param string $targetDir
     */
    private static function moveLegacyLogs($targetDir)
    {
        $legacyDir = rtrim(_PS_ROOT_DIR_, '/\\') . '/' . self::LEGACY_LOG_DIR;
        self::protectDirectory($legacyDir);

        $legacyFiles = glob($legacyDir . '*.log');

        if (empty($legacyFiles)) {
            return;
        }

        foreach ($legacyFiles as $legacyFile) {
            $target = $targetDir . basename($legacyFile);
            if (file_exists($target)) {
                $target = $targetDir . 'legacy-' . basename($legacyFile);
            }

            if (@rename($legacyFile, $target)) {
                continue;
            }

            // rename() can fail across filesystems; copy, then remove the original.
            // If even that fails the file stays behind the deny-all .htaccess.
            if (@copy($legacyFile, $target)) {
                @unlink($legacyFile);
            }
        }
    }
}
