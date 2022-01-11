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

/**
 * Class ViaBillOrderCapture
 */
class ViaBillOrderCapture extends ObjectModel
{
    /**
     * Order Id Variable Declaration.
     *
     * @var
     */
    public $id_order;

    /**
     * Amount Variable Declaration.
     *
     * @var
     */
    public $amount;

    /**
     * Date Added Variable Declaration.
     *
     * @var
     */
    public $date_add;

    /**
     * Date Updated Variable Declaration.
     *
     * @var
     */
    public $date_upd;

    /**
     * Sets ViaBill Order Capture Entity Definitions.
     *
     * @var array
     */
    public static $definition = [
        'table' => 'viabill_order_capture',
        'primary' => 'id_viabill_order_capture',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'date_add' => ['type' => self::TYPE_DATE],
            'date_upd' => ['type' => self::TYPE_DATE],
        ],
    ];

    /**
     * Selects Primary Key From viabill_order_capture DB Table By Given Order ID.
     *
     * @param int $idOrder
     *
     * @return int
     */
    public static function getPrimaryKey($idOrder)
    {
        $query = new DbQuery();
        $query->select(pSQL(self::$definition['primary']));
        $query->from(pSQL(self::$definition['table']));
        $query->where('`id_order`=' . (int) $idOrder);

        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Gets Count Of Captured Orders From viabill_order_capture DB Table.
     *
     * @return int
     */
    public function getCapturedOrdersCount()
    {
        $query = new DbQuery();
        $query->select('COUNT(`id_order`)');
        $query->from(pSQL(self::$definition['table']));
        $query->where('id_order=' . (int) $this->id_order);

        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Gets Total Capture Sum Of Given Order From viabill_order_capture DB Table.
     *
     * @return float
     */
    public function getTotalCaptured()
    {
        $query = new DbQuery();
        $query->select('SUM(`amount`)');
        $query->from(pSQL(self::$definition['table']));
        $query->where('id_order=' . (int) $this->id_order);

        return (float) Db::getInstance()->getValue($query);
    }
}
