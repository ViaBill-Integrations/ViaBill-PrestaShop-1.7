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

namespace ViaBill\Object\Validator;

/**
 * Class ValidationResponse
 */
class ValidationResponse
{
    /**
     * Validation Accepted Variable Declaration.
     *
     * @var bool
     */
    private $validationAccepted;

    /**
     * ValidationResponse constructor.
     *
     * @param bool $validationAccepted
     */
    public function __construct($validationAccepted)
    {
        $this->validationAccepted = $validationAccepted;
    }

    /**
     * Checks If Validation Is Accepted.
     *
     * @return bool
     */
    public function isValidationAccepted()
    {
        return $this->validationAccepted;
    }
}
