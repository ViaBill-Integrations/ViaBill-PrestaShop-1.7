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

namespace ViaBill\Adapter;

/**
 * Class Tools
 *
 * @package ViaBill\Adapter
 */
class Tools
{
    /**
     * Turns String To Uppercase.
     *
     * @param string $string
     *
     * @return bool|mixed|null|string|string[]
     */
    public function strToUpper($string)
    {
        return \Tools::strtoupper($string);
    }

    /**
     * Checks If Link Is Submited.
     *
     * @param string $link
     *
     * @return bool
     */
    public function isSubmit($link)
    {
        return \Tools::isSubmit($link);
    }

    /**
     * Redirects To Link Location.
     *
     * @param string $redirectLink
     */
    public function redirect($redirectLink)
    {
        \Tools::redirect($redirectLink);
    }

    /**
     * Gets Value From $_POST / $_GET
     *
     * @param string $value
     *
     * @return mixed
     */
    public function getValue($value)
    {
        return \Tools::getValue($value);
    }

    /**
     * GetIsset Value From $_POST / $_GET
     *
     * @param string $value
     *
     * @return mixed
     */
    public function getIsset($value)
    {
        return \Tools::getIsset($value);
    }

    /**
     * Encrypts Password.
     *
     * @param string $value
     *
     * @return string
     */
    public function encrypt($value)
    {
        return \Tools::hash($value);
    }

    /**
     * Allows To Get The Tontent From Either A URL Or A Local File.
     *
     * @param string $url
     *
     * @return bool|string
     */
    public function fileGetContents($url)
    {
        return \Tools::file_get_contents($url);
    }

    /**
     * Formats Number.
     *
     * @param float $number
     *
     * @return string
     */
    public function displayNumber($number)
    {
        return \Tools::displayNumber($number);
    }

    /**
     * Return Price With Currency Sign For A Given Product.
     *
     * @param float $amount
     * @param null $currency
     *
     * @return string
     */
    public function displayPrice($amount, $currency = null)
    {
        return \Tools::displayPrice($amount, $currency);
    }
}
