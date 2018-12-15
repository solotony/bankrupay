<?php
/*
* 2018 Antonio Solo
*
*  @author Antonio Solo <as@solotony.com>
*  @copyright  2018 Antonio Solo
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
    exit;

class BankRuPay extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    public function __construct()
    {
        $this->name = 'bankrupay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Antonio Solo';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 0;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array(
            'BANK_RUPAY_ORG',
            'BANK_RUPAY_INN',
            'BANK_RUPAY_KPP',
            'BANK_RUPAY_RS',
            'BANK_RUPAY_BANK',
            'BANK_RUPAY_BIK',
            'BANK_RUPAY_KS'
        ));
        if (!empty($config['BANK_RUPAY_ORG']))      $this->org = $config['BANK_RUPAY_ORG'];
        if (!empty($config['BANK_RUPAY_INN']))      $this->inn = $config['BANK_RUPAY_INN'];
        if (!empty($config['BANK_RUPAY_KPP']))      $this->kpp = $config['BANK_RUPAY_KPP'];
        if (!empty($config['BANK_RUPAY_RS']))       $this->rs = $config['BANK_RUPAY_RS'];
        if (!empty($config['BANK_RUPAY_BANK']))     $this->bank = $config['BANK_RUPAY_BANK'];
        if (!empty($config['BANK_RUPAY_BIK']))      $this->bik = $config['BANK_RUPAY_BIK'];
        if (!empty($config['BANK_RUPAY_KS']))       $this->ks = $config['BANK_RUPAY_KS'];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Безналичный платеж');
        $this->description = $this->l('Прием безналичных платежей на расчетный счет в российском банке');
        $this->confirmUninstall = $this->l('Ты хорошо подумал, друг?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');

        if (!isset($this->org) || !isset($this->inn) || !isset($this->rs) || !isset($this->bank) || !isset($this->bik) || !isset($this->ks))
            $this->warning = $this->l('Надо настроить банковские реквизиты ');
        if (!count(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency has been set for this module.');

        $this->extra_mail_vars = array(
            '{bankrupay_org}' => Configuration::get('BANK_RUPAY_ORG'),
            '{bankrupay_inn}' => nl2br(Configuration::get('BANK_RUPAY_INN')),
            '{bankrupay_kpp}' => nl2br(Configuration::get('BANK_RUPAY_KPP')),
            '{bankrupay_rs}' => nl2br(Configuration::get('BANK_RUPAY_RS')),
            '{bankrupay_bank}' => nl2br(Configuration::get('BANK_RUPAY_BANK')),
            '{bankrupay_bik}' => nl2br(Configuration::get('BANK_RUPAY_BIK')),
            '{bankrupay_ks}' => nl2br(Configuration::get('BANK_RUPAY_KS'))
        );
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('payment') || ! $this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('BANK_RUPAY_ORG')
            || !Configuration::deleteByName('BANK_RUPAY_INN')
            || !Configuration::deleteByName('BANK_RUPAY_KPP')
            || !Configuration::deleteByName('BANK_RUPAY_RS')
            || !Configuration::deleteByName('BANK_RUPAY_BANK')
            || !Configuration::deleteByName('BANK_RUPAY_BIK')
            || !Configuration::deleteByName('BANK_RUPAY_KS')
            || !parent::uninstall())
            return false;
        return true;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            if (!Tools::getValue('BANK_RUPAY_ORG'))
                $this->_postErrors[] = $this->l('Получатель платежа должен быть указан.');
            elseif (!Tools::getValue('BANK_RUPAY_INN'))
                $this->_postErrors[] = $this->l('ИНН должен быть указан.');
            //elseif (!Tools::getValue('BANK_RUPAY_KPP'))
            //    $this->_postErrors[] = $this->l('КПП должен быть указан.');
            elseif (!Tools::getValue('BANK_RUPAY_RS'))
                $this->_postErrors[] = $this->l('Расчетный счет должен быть указан.');
            elseif (!Tools::getValue('BANK_RUPAY_BANK'))
                $this->_postErrors[] = $this->l('Банк должен быть указан.');
            elseif (!Tools::getValue('BANK_RUPAY_BIK'))
                $this->_postErrors[] = $this->l('БИК должен быть указан.');
            elseif (!Tools::getValue('BANK_RUPAY_KS'))
                $this->_postErrors[] = $this->l('Корреспондентский счет должен быть указан.');
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            Configuration::updateValue('BANK_RUPAY_ORG',    Tools::getValue('BANK_RUPAY_ORG'));
            Configuration::updateValue('BANK_RUPAY_INN',    Tools::getValue('BANK_RUPAY_INN'));
            Configuration::updateValue('BANK_RUPAY_KPP',    Tools::getValue('BANK_RUPAY_KPP'));
            Configuration::updateValue('BANK_RUPAY_RS',     Tools::getValue('BANK_RUPAY_RS'));
            Configuration::updateValue('BANK_RUPAY_BANK',   Tools::getValue('BANK_RUPAY_BANK'));
            Configuration::updateValue('BANK_RUPAY_BIK',    Tools::getValue('BANK_RUPAY_BIK'));
            Configuration::updateValue('BANK_RUPAY_KS',     Tools::getValue('BANK_RUPAY_KS'));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    protected function _displayBankRuPay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            $this->_postValidation();
            if (!count($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= $this->displayError($err);
        }
        else
            $this->_html .= '<br />';

        $this->_html .= $this->_displayBankRuPay();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPayment($params)
    {
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active)
            return;

        if (!$this->checkCurrency($params['cart']))
            return;

        $payment_options = array(
            'cta_text' => $this->l('Безналичный платеж'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/bankrupay.jpg'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;

        $state = $params['objOrder']->getCurrentState();
        if (in_array($state, array(Configuration::get('PS_OS_BANKWIRE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))))
        {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'bankrupayOrg' => $this->org,
                'bankrupayInn' => Tools::nl2br($this->inn),
                'bankrupayKpp' => Tools::nl2br($this->kpp),
                'bankrupayRs' => Tools::nl2br($this->rs),
                'bankrupayBank' => Tools::nl2br($this->bank),
                'bankrupayBik' => Tools::nl2br($this->bik),
                'bankrupayKs' => Tools::nl2br($this->ks),
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
                $this->smarty->assign('reference', $params['objOrder']->reference);
        }
        else
            $this->smarty->assign('status', 'failed');
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Contact details'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Получатель платежа'),
                        'name' => 'BANK_RUPAY_ORG',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('ИНН'),
                        'name' => 'BANK_RUPAY_INN',
                        'desc' => $this->l('ИНН 10 или 12 символов'),
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('КПП'),
                        'name' => 'BANK_RUPAY_KPP',
                        'required' => false
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Расчетный счет'),
                        'name' => 'BANK_RUPAY_RS',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Банк'),
                        'name' => 'BANK_RUPAY_BANK',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('БИК'),
                        'name' => 'BANK_RUPAY_BIK',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Корреспондентский счет'),
                        'name' => 'BANK_RUPAY_KS',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BANK_RUPAY_ORG'    => Tools::getValue('BANK_RUPAY_ORG',    Configuration::get('BANK_RUPAY_ORG')),
            'BANK_RUPAY_INN'    => Tools::getValue('BANK_RUPAY_INN',    Configuration::get('BANK_RUPAY_INN')),
            'BANK_RUPAY_KPP'    => Tools::getValue('BANK_RUPAY_KPP',    Configuration::get('BANK_RUPAY_KPP')),
            'BANK_RUPAY_RS'     => Tools::getValue('BANK_RUPAY_RS',     Configuration::get('BANK_RUPAY_RS')),
            'BANK_RUPAY_BANK'   => Tools::getValue('BANK_RUPAY_BANK',   Configuration::get('BANK_RUPAY_BANK')),
            'BANK_RUPAY_BIK'    => Tools::getValue('BANK_RUPAY_BIK',    Configuration::get('BANK_RUPAY_BIK')),
            'BANK_RUPAY_KS'     => Tools::getValue('BANK_RUPAY_KS',     Configuration::get('BANK_RUPAY_KS')),
        );
    }
}
