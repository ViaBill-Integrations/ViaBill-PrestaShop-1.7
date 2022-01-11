<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Repository;

use ObjectModel;
use PrestaShopCollection;

interface ReadOnlyRepositoryInterface
{
    /**
     * @return PrestaShopCollection
     */
    public function findAll();

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ObjectModel|null
     */
    public function findOneBy(array $keyValueCriteria);
}
