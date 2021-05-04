{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{extends file="helpers/options/options.tpl"}

{block name="input" append}
    {if $field['type'] == 'orders_status_multiselect'}
      <div class="col-lg-9 {if isset($field['class'])}{$field['class']}{/if}">
          {include file="../../../partials/order-status-multiselect.tpl"}
      </div>
    {/if}
{/block}