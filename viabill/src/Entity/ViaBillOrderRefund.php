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
 * Class ViaBillOrderRefund
 */
class ViaBillOrderRefund extends ObjectModel
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
     * Sets ViaBill Order Refund Entity Definitions.
     *
     * @var array
     */
    public static $definition = array(
        'table'   => 'viabill_order_refund',
        'primary' => 'id_viabill_order_refund',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'date_add' => array('type' => self::TYPE_DATE),
            'date_upd' => array('type' => self::TYPE_DATE)
        )
    );

    /**
     * Gets id_viabill_order_refund From viabill_order_refund Table By Order ID.
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
        $query->where('`id_order`='.(int) $idOrder);

        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Gets Count Of Refunds From order_refund Table By Order Id.
     *
     * @return int
     */
    public function getRefundedOrdersCount()
    {
        $query = new DbQuery();
        $query->select('COUNT(`id_order`)');
        $query->from(pSQL(self::$definition['table']));
        $query->where('id_order='.(int) $this->id_order);
        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Gets Total Sum Of Refunds From order_refund Table By Order Id.
     *
     * @return float
     */
    public function getTotalRefunded()
    {
        $query = new DbQuery();
        $query->select('SUM(`amount`)');
        $query->from(pSQL(self::$definition['table']));
        $query->where('id_order='.(int) $this->id_order);
        return (float) Db::getInstance()->getValue($query);
    }
}
