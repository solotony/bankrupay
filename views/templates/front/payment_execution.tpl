{*
* 2018 Antonio Solo
*
*  @author Antonio Solo <as@solotony.com>
*  @copyright  2018 Antonio Solo
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='bankrupay'}">{l s='Checkout' mod='bankrupay'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Bank-wire payment' mod='bankrupay'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='bankrupay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='bankrupay'}</p>
{else}

<h3>{l s='Bank-wire payment' mod='bankrupay'}</h3>
<form action="{$link->getModuleLink('bankrupay', 'validation', [], true)|escape:'html'}" method="post">
<p>
	<img src="{$this_path_bw}bankrupay.jpg" alt="{l s='Bank wire' mod='bankrupay'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by bank wire.' mod='bankrupay'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='bankrupay'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='bankrupay'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='bankrupay'}
    {/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We allow several currencies to be sent via bank wire.' mod='bankrupay'}
		<br /><br />
		{l s='Choose one of the following:' mod='bankrupay'}
		<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
	{else}
		{l s='We allow the following currency to be sent via bank wire:' mod='bankrupay'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	{/if}
</p>
<p>
	{l s='Bank wire account information will be displayed on the next page.' mod='bankrupay'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "I confirm my order".' mod='bankrupay'}</b>
</p>
<p class="cart_navigation" id="cart_navigation">
	<input type="submit" value="{l s='I confirm my order' mod='bankrupay'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='bankrupay'}</a>
</p>
</form>
{/if}
