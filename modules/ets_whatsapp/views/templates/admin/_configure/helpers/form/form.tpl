{*
* Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{extends file="helpers/form/form.tpl"}
{block name="label"}
	{if isset($input.label)}
		<label class="control-label col-lg-3 {if (isset($input.required) && $input.required && $input.type != 'radio') || (isset($input.showRequired) && $input.showRequired)} required{/if}">
			{if isset($input.hint)}
			<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
						{foreach $input.hint as $hint}
							{if is_array($hint)}
								{$hint.text|escape:'html':'UTF-8'}
							{else}
								{$hint|escape:'html':'UTF-8'}
							{/if}
						{/foreach}
					{else}
						{$input.hint|escape:'html':'UTF-8'}
					{/if}">
			{/if}
			{$input.label|escape:'html':'UTF-8'}
			{if isset($input.hint)}
			</span>
			{/if}
		</label>
	{/if}
{/block}
{block name="field"}
    {$smarty.block.parent}
    {if $input.type == 'file' && $input.name=='ETS_WA_ICON'}
        <label class="control-label col-lg-3 ets_wa_uploaded_label{if !$display_icon} hidden{/if}">{l s='Uploaded image:' mod='ets_whatsapp'}</label>
        <div class="col-lg-9 ets_wa_uploaded_preview{if !$display_icon} hidden{/if}">
            {if $display_icon}
                <img class="ets_wa_uploaded_img" src="{$display_icon|escape:'html':'UTF-8'}" />
            {/if}
            {if isset($icon_delete_link) && $icon_delete_link}
                <a class="ets_wa_icon_delete" href="#" data-delete-url="{$icon_delete_link|escape:'html':'UTF-8'}" title="{l s='Delete' mod='ets_whatsapp'}"><i class="process-icon-delete"></i></a>
            {/if}
        </div>
    {/if}
{/block}
{block name='input_row'}
    {if $input.name =='ETS_WA_ADJUST_RIGHT' || $input.name=='ETS_WA_ADJUST_BOTTOM' || $input.name=='ETS_WA_ADJUST_LEFT'}
        {if $input.name=='ETS_WA_ADJUST_RIGHT'}
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Adjust display position' mod='ets_whatsapp'} </label>
                <div class="col-lg-9 form-group">
                    <div class="col-sm-4 col-md-3 col-lg-2 ETS_WA_ADJUST_RIGHT_item">
                        <label class="control-label"> {l s='Right padding' mod='ets_whatsapp'} </label>
                        <div class="input-group col-lg-12">
                            <input id="ETS_WA_ADJUST_RIGHT" class="" name="ETS_WA_ADJUST_RIGHT" value="{$fields_value.ETS_WA_ADJUST_RIGHT|escape:'html':'UTF-8'}" type="text" />
                            <span class="input-group-addon"> px </span>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-3 col-lg-2 ETS_WA_ADJUST_RIGHT_item">
                        <label class="control-label"> {l s='Bottom padding' mod='ets_whatsapp'} </label>
                        <div class="input-group col-lg-12">
                            <input id="ETS_WA_ADJUST_BOTTOM" class="" name="ETS_WA_ADJUST_BOTTOM" value="{$fields_value.ETS_WA_ADJUST_BOTTOM|escape:'html':'UTF-8'}" type="text" />
                            <span class="input-group-addon"> px </span>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-3 col-lg-2 ETS_WA_ADJUST_RIGHT_item">
                        <label class="control-label"> {l s='Left padding' mod='ets_whatsapp'} </label>
                        <div class="input-group col-lg-12">
                            <input id="ETS_WA_ADJUST_LEFT" class="" name="ETS_WA_ADJUST_LEFT" value="{$fields_value.ETS_WA_ADJUST_LEFT|escape:'html':'UTF-8'}" type="text" />
                            <span class="input-group-addon"> px </span>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    {elseif $input.name =='ETS_WA_CALL_PREFIX' || $input.name=='ETS_WA_NUMBER_PHONE'}
        {if $input.name=='ETS_WA_NUMBER_PHONE'}
            <div class="form-group">
                <input type="hidden" name="ETS_WA_CALL_PREFIX" id="ETS_WA_CALL_PREFIX" value="{$fields_value.ETS_WA_CALL_PREFIX|escape:'html':'UTF-8'}"/>
                <label class="control-label col-lg-3"> {$input.label|escape:'html':'UTF-8'} </label>
                <div class="input-group ets-wa-group-number-phone">
                    <span class="input-group-addon country">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-flip="false"><img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ets_whatsapp/views/img/`$country_iso_code|lower|escape:'htmlall':'UTF-8'`")}.gif" />&nbsp; {$country_name|escape:'html':'UTF-8'} </button>
                            <div class="dropdown-menu js-choice-options">
                                {if $countries}
                                    {foreach from =$countries item='country'}
                                        <button type="button" class="js-dropdown-item dropdown-item" data-call_prefix="{$country.call_prefix|escape:'html':'UTF-8'}" data-value="{$country.id_country|intval}"><img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ets_whatsapp/views/img/`$country.iso_code|lower|escape:'htmlall':'UTF-8'`")}.gif" />&nbsp;<span>{$country.name|escape:'html':'UTF-8'}</span></button>
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                    </span>
                    <span class="input-group-addon call_prefix">+{$call_prefix|escape:'html':'UTF-8'}</span>
                    <input type="text" value="{$fields_value.ETS_WA_NUMBER_PHONE|escape:'html':'UTF-8'}" name="ETS_WA_NUMBER_PHONE" id="ETS_WA_NUMBER_PHONE" />
                </div>
            </div>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
