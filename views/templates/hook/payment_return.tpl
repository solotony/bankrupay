{*
* 2018 Antonio Solo
*
*  @author Antonio Solo <as@solotony.com>
*  @copyright  2018 Antonio Solo
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $status == 'ok'}
<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='bankrupay'}
		<br /><br />
		{l s='Please send us a bank wire with' mod='bankrupay'}
		<br />- {l s='Сумма к оплате' mod='bankrupay'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br />- {l s='Получатель платежа' mod='bankrupay'}  <strong>{if $bankrupayOrg}{$bankrupayOrg}{else}___________{/if}</strong>
		<br />- {l s='ИНН' mod='bankrupay'}  <strong>{if $bankrupayInn}{$bankrupayInn}{else}___________{/if}</strong>
		<br />- {l s='КПП' mod='bankrupay'}  <strong>{if $bankrupayKpp}{$bankrupayKpp}{else}___________{/if}</strong>
		<br />- {l s='Расчетный счет' mod='bankrupay'}  <strong>{if $bankrupayRs}{$bankrupayRs}{else}___________{/if}</strong>
		<br />- {l s='Банк' mod='bankrupay'}  <strong>{if $bankrupayBank}{$bankrupayBank}{else}___________{/if}</strong>
		<br />- {l s='БИК' mod='bankrupay'}  <strong>{if $bankrupayBik}{$bankrupayBik}{else}___________{/if}</strong>
		<br />- {l s='Корреспондентский счет' mod='bankrupay'}  <strong>{if $bankrupayKs}{$bankrupayKs}{else}___________{/if}</strong>
		<br />- {l s='Назначение платежа' mod='bankrupay'}  <strong>{l s='Оплата заказа %s'  sprintf=$id_order  mod='bankrupay'}</strong>

		<br /><br />{l s='An email has been sent with this information.' mod='bankrupay'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='bankrupay'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='bankrupay'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='bankrupay'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='bankrupay'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='bankrupay'}</a>.
	</p>
{/if}
