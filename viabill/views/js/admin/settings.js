/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

$(document).ready(function() {
    setGoToMyViaBillTargetBlank();

    $(document).on('input', 'input[name="VB_ENABLE_AUTO_PAYMENT_CAPTURE"]', changeOrderStatusMultiselectVisibility);

    function setGoToMyViaBillTargetBlank() {
        $('.js-go-to-viabill').attr('target', '_blank');
    }

    function changeOrderStatusMultiselectVisibility() {
        var orderStatusMultiselect = $('.js-order-status-multiselect');
        var autoPaymentCaptureVal = $(this).val();

        if (parseInt(autoPaymentCaptureVal)) {
            orderStatusMultiselect.show('100');
        } else {
            orderStatusMultiselect.hide();
        }
    }
});