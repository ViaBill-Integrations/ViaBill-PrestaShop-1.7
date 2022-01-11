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
use PrestaShopException;

class AbstractRepository implements ReadOnlyRepositoryInterface
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    /**
     * @param string $fullyClassifiedClassName
     */
    public function __construct($fullyClassifiedClassName)
    {
        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    public function findAll()
    {
        return new PrestaShopCollection($this->fullyClassifiedClassName);
    }

    /**
     * @param array $keyValueCriteria
     *
     * @return ObjectModel|null
     *
     * @throws PrestaShopException
     */
    public function findOneBy(array $keyValueCriteria)
    {
        $psCollection = new PrestaShopCollection($this->fullyClassifiedClassName);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $results = $psCollection->getAll();
        if (!empty($results)) {
            if (isset($results[0])) {
                return $results[0];
            }
        }

        return null;

        /*
        $first = $psCollection->getFirst();

        return false === $first ? null: $first;
        */
    }
}
