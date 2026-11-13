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
    {* Read-only legal note in the selected language; it has no name, so nothing is posted *}
    {if $field['type'] == 'viabill_legal_note_text'}
      <div class="col-lg-9">
        <textarea id="{$key|escape:'html':'UTF-8'}" rows="2" readonly="readonly" style="background-color: #f6f7f7;"
                  data-language-select="{$field['language_select']|escape:'html':'UTF-8'}"
                  data-texts="{$field['texts']|json_encode|escape:'html':'UTF-8'}">{$field['text']|escape:'html':'UTF-8'}</textarea>
      </div>
      <script>
        (function () {
          // The language select comes later in the form, so listen on the document.
          var text = document.getElementById('{$key|escape:'javascript':'UTF-8'}');
          var selectId = text.getAttribute('data-language-select');
          var texts = JSON.parse(text.getAttribute('data-texts') || '{ldelim}{rdelim}');
          document.addEventListener('change', function (event) {
            var select = event.target;
            if (select && select.id === selectId && Object.prototype.hasOwnProperty.call(texts, select.value)) {
              text.value = texts[select.value];
            }
          });
        })();
      </script>
    {/if}
{/block}