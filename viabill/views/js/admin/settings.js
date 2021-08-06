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

    if ($("#DisableThirdPartyPaymentBtn").length) {
        $("#DisableThirdPartyPaymentBtn").click(function() {
            var disable_third_party_gateway_url = $('#thirdparty_disable_url').val();
            console.log(disable_third_party_gateway_url);
            $.ajax({
                method: 'get',
                url: disable_third_party_gateway_url,
                data: null,
                dataType: "text",
                success: function(data) {
                    alert(data); 
                    location.reload();
                },
                error: function(e) {
                    console.log(e);
                }
            });             
        });
    }

});