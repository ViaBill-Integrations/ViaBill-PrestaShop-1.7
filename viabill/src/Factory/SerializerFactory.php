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

namespace ViaBill\Factory;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class SerializerFactory
 */
class SerializerFactory
{
    /**
     * Gets New Instance Of Serializer Class That Serializes And Deserializes Data.
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new JsonEncoder()];

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }
}
