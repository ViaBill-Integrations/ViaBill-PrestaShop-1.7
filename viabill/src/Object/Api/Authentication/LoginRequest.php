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

namespace ViaBill\Object\Api\Authentication;

/**
 * Class LoginRequest
 *
 * @package ViaBill\Object\Api\Authentication
 */
class LoginRequest
{
    /**
     * User Email Variable Declaration.
     *
     * @var string
     */
    private $email;

    /**
     * User Password Variable Declaration.
     *
     * @var string
     */
    private $password;

    /**
     * LoginRequest constructor.
     *
     * @param string $email
     * @param string $password
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Gets User Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets User Password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
