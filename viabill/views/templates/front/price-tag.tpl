{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{if $dynamicPriceTrigger}
    <input type="hidden" id="{$dynamicPriceTrigger}">
{/if}

<div
        class="viabill-pricetag"
        data-view="{$dataView}"
        {if !$dynamicPriceSelector}data-price="{$dataPrice}"{/if}
        {if $dynamicPriceSelector}
            data-dynamic-price="{$dynamicPriceSelector}"
            data-dynamic-price-triggers="#{$dynamicPriceTrigger}"
        {/if}
        data-language="{$dataLanguageIso}"
        data-currency="{$dataCurrencyIso}"
        data-country-code="{$dataCountryCodeIso}"
></div>