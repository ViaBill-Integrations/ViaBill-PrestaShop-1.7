{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

<div class="terms-and-conditions-container">
    <p class="js-terms-error terms-and-conditions-error hidden">
        {l s='Please read and accept Terms And Conditions' mod='viabill'}
    </p>
    <input type="checkbox" class="js-terms-checkbox terms-and-conditions-checkbox" name="terms_and_conditions" value="1" title="terms_and_conditions">
    <p class="terms-and-conditions-text">
        <span>{l s='I have read and accept' mod='viabill'}</span>
        <a href="{$termsLink}{$termsLinkCountry}" class="terms-and-conditions-link" target="_blank">
            {l s='Terms And Conditions' mod='viabill'}
        </a>
    </p>
</div>