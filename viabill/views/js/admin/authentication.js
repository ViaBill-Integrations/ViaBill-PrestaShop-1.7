/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

$(document).ready(function() {
    setAuthButtonTargetBlank();

    $(document).on('change', '.js-country-select', changeTermsLink);

    $(document).on('click', 'button[name="submitRegisterForm"]', checkIfTermsAccepted);

    function setAuthButtonTargetBlank() {
        $('.vd-auth-additional-button').attr('target', '_blank');
    }
    
    function changeTermsLink() {
        var selectedCountryISO = $(this).val();

        if (selectedCountryISO) {
            $(".terms-and-conditions-link").attr("href", termsLink + '#' + selectedCountryISO)
        } else {
            $(".terms-and-conditions-link").attr("href", termsLink)
        }
    }
    
    function checkIfTermsAccepted() {
        $('#configuration_form.AdminViaBillAuthentication').submit(function(e){
            if ($('input.js-terms-checkbox').is(':checked') != true) {
                e.preventDefault();

                $(".js-terms-error").removeClass('hidden');
            } else {
                $(".js-terms-error").not('hidden').addClass('hidden');
            }
        });
    }
});