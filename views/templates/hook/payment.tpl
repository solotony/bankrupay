{*
* 2018 Antonio Solo
*
*  @author Antonio Solo <as@solotony.com>
*  @copyright  2018 Antonio Solo
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<p class="payment_module">
	<a href="{$link->getModuleLink('bankrupay', 'payment')|escape:'html'}" title="{l s='Pay by bank wire' mod='bankrupay'}">
		<img src="{$this_path_bw}bankrupay.jpg" alt="{l s='Pay by bank wire' mod='bankrupay'}" width="86" height="49"/>
		{l s='Pay by bank wire' mod='bankrupay'}
	</a>
</p>