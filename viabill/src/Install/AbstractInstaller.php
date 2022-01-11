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

use Db;
use Exception;

/**
 * Class AbstractInstaller
 */
abstract class AbstractInstaller
{
    /**
     * Gets SQL Statements.
     *
     * @param array $sqlFile
     *
     * @return mixed
     */
    abstract protected function getSqlStatements($sqlFile);

    /**
     * Executes Given Database Query
     *
     * @param Db $database
     * @param array $sqlStatements
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function execute(Db $database, $sqlStatements)
    {
        try {
            $result = $database->getInstance()->execute($sqlStatements);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return (bool) $result;
    }
}
