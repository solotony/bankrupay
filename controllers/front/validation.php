<?php
/*
* 2018 Antonio Solo
*
*  @author Antonio Solo <as@solotony.com>
*  @copyright  2018 Antonio Solo
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class BankrupayValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'bankrupay')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
            '{bankrupay_org}' => Configuration::get('BANK_RUPAY_ORG'),
            '{bankrupay_inn}' => nl2br(Configuration::get('BANK_RUPAY_INN')),
            '{bankrupay_kpp}' => nl2br(Configuration::get('BANK_RUPAY_KPP')),
            '{bankrupay_rs}' => nl2br(Configuration::get('BANK_RUPAY_RS')),
            '{bankrupay_bank}' => nl2br(Configuration::get('BANK_RUPAY_BANK')),
            '{bankrupay_bik}' => nl2br(Configuration::get('BANK_RUPAY_BIK')),
            '{bankrupay_ks}' => nl2br(Configuration::get('BANK_RUPAY_KS'))
		);

		$this->module->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}
