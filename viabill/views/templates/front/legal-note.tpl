{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{* $legalNoteText is clean HTML, see LegalNoteService::getText(). Styled in views/css/front/legal-note.css
   and by the styling settings of the module, see LegalNoteService::getStyle() *}
<div class="viabill-legal-note"{if !empty($legalNoteStyle)} style="{$legalNoteStyle|escape:'htmlall':'UTF-8'}"{/if}>
    {$legalNoteText nofilter}
</div>
