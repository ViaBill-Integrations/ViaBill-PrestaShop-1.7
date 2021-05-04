/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

$(document).ready(function () {
    var $optionInput = $('[data-module-name="viabill"]');
    var $container = $optionInput.closest('.payment-option');
    $container.css('position', 'relative');
    var $image = $container.find('img[src$="viabill.png"]');

    $image.css({
        'position': 'absolute',
        'top': '-9px',
        'padding-left': '5px'
    });
});