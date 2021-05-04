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

/**
 * Class ViaBillOrder.
 */
class ViaBillOrder extends ObjectModel
{
    /**
     * Order Id Variable Declaration.
     *
     * @var
     */
    public $id_order;

    /**
     * Currency Id Variable Declaration.
     *
     * @var
     */
    public $id_currency;

    /**
     * Sets ViaBill Order Entity Definitions.
     *
     * @var array
     */
    public static $definition = array(
        'table'   => 'viabill_order',
        'primary' => 'id_viabill_order',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt')
        )
    );

    /**
     * Selects Primary Key From viabill_order DB Table By Given Order ID.
     *
     * @param int $idOrder
     *
     * @return false|null|string
     */
    public static function getPrimaryKey($idOrder)
    {
        $query = new DbQuery();
        $query->select(pSQL(self::$definition['primary']));
        $query->from(pSQL(self::$definition['table']));
        $query->where('id_order='.(int) $idOrder);
        return Db::getInstance()->getValue($query);
    }

    /**
     * Gets All Order Ids From viabill_order DB Table.
     *
     * @return array
     */
    public static function getOrderIds()
    {
        $query = new DbQuery();
        $query->select('`id_order`');
        $query->from(pSQL(self::$definition['table']));
        $resource = Db::getInstance()->query($query);
        $ids = array();
        while ($row = Db::getInstance()->nextRow($resource)) {
            $ids[] = $row['id_order'];
        }
        return $ids;
    }
}
