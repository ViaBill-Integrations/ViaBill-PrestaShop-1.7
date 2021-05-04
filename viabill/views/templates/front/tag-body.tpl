{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

<div class="price-tag-global-container">
    {if $useExtraGap}
        <div class="clearfix">&nbsp;</div>
    {/if}

    <div class="price-tag-container {if $useColumns}col-xs-12{/if}">
        {include
        file='./price-tag.tpl'
        dataView=$dataView
        dataPrice=$dataPrice
        dataLanguageIso=$dataLanguageIso
        dataCurrencyIso=$dataCurrencyIso
        dataCountryCodeIso=$dataCountryCodeIso
        dynamicPriceSelector=$dynamicPriceSelector
        dynamicPriceTrigger=$dynamicPriceTrigger
        }
    </div>

    {if $useExtraGap}
        <div class="clearfix">&nbsp;</div>
    {/if}
</div>