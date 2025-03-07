<?php
/**
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above PCTECKSERV notice.
 *  *
 *  *
 * ***********************************************************************
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Paymentgateways extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('billing_model', 'billing');
        $this->load->model('invoices_model', 'invoices');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if ($this->aauth->get_user()->roleid < 5) {

            exit($this->lang->line('translate19'));

        }
    }

    public function index()
    {

        $data['gateway'] = $this->billing->gateway_list();
        $this->load->view('fixed/header');
        $this->load->view('payment/list', $data);
        $this->load->view('fixed/footer');
    }


    public function edit()
    {
        if ($this->input->post()) {

            $gid = $this->input->post('gid');
            $currency = $this->input->post('currency');
            $key1 = $this->input->post('key1');
            $key2 = $this->input->post('key2');
            $enable = $this->input->post('enable');
            $devmode = $this->input->post('devmode');
            $p_fee = $this->input->post('p_fee');

            if ($key2 == '') {
                $key2 = 'none';
            }

            $this->billing->gateway_update($gid, $currency, $key1, $key2, $enable, $devmode, $p_fee);

        } else {

            $id = intval($this->input->get('id'));
            $data['gateway'] = $this->billing->gateway($id);
            $this->load->view('fixed/header');
            $this->load->view('payment/gateway-edit', $data);
            $this->load->view('fixed/footer');

        }

    }


    public function settings()
    {
        $this->load->model('plugins_model', 'plugins');
        if ($this->input->post()) {

            $id = $this->input->post('account');
            $enable = $this->input->post('enable');
            $bank_enable = $this->input->post('bank');
            $auto_debit = $this->input->post('auto_debit');
            $this->billing->payment_settings($id, $enable, $bank_enable);
            $this->plugins->m_update_api(69, null, $auto_debit, null,null,null,null, false);
			$this->aauth->applog("[Configurações de negócios->Configurações de Pagamento] Dados Atualizados", $this->aauth->get_user()->username);
        } else {
			$this->load->model('accounts_model');
			
			$this->load->model('settings_model', 'settings');
			$discship = [];
			if($this->aauth->get_user()->loc == 0)
			{
				$discship = $this->settings->online_pay_settings_main();
			}else{
				$discship = $this->settings->online_pay_settings($this->aauth->get_user()->loc);
			}
            $data['current'] = $discship;
            $data['online_pay'] = $this->billing->online_pay_settings();
			$data['acclist'] = $this->accounts_model->accountslist();
            $this->load->view('fixed/header');
            $this->load->view('payment/settings', $data);
            $this->load->view('fixed/footer');
        }
    }

    function bank_accounts()
    {

        $data['bank_accounts'] = $this->billing->bank_accounts();
        $this->load->view('fixed/header');
        $this->load->view('payment/bank_list', $data);
        $this->load->view('fixed/footer');
    }


    public function add_bank_ac()
    {
        if ($this->input->post()) {


            $name = $this->input->post('name', true);
            $acn = $this->input->post('acn', true);
            $code = $this->input->post('code', true);
            $enable = $this->input->post('enable');
            $branch = $this->input->post('branch', true);
            $address = $this->input->post('address', true);
            $bank = $this->input->post('bank', true);

            $this->billing->bank_ac_add($name, $acn, $code, $enable, $bank, $branch, $address);

        } else {

            $head['title'] = "Add Bank Account";
            $this->load->view('fixed/header', $head);
            $this->load->view('payment/bank-add');
            $this->load->view('fixed/footer');

        }

    }


    public function edit_bank_ac()
    {
        if ($this->input->post()) {

            $gid = $this->input->post('gid');
            $name = $this->input->post('name', true);
            $acn = $this->input->post('acn', true);
            $code = $this->input->post('code', true);
            $enable = $this->input->post('enable', true);
            $branch = $this->input->post('branch', true);
            $address = $this->input->post('address', true);
            $bank = $this->input->post('bank', true);

            $this->billing->bank_ac_update($gid, $name, $acn, $code, $enable, $bank, $branch, $address);

        } else {

            $id = intval($this->input->get('id'));
            $head['title'] = $this->lang->line('Edit Bank Account');
            $data['bank_account'] = $this->billing->bank_account_info($id);
            $this->load->view('fixed/header', $head);
            $this->load->view('payment/bank-edit', $data);
            $this->load->view('fixed/footer');

        }
    }


    function currencies()
    {
        $data['currency_list'] = $this->invoices->currencies();
        $this->load->view('fixed/header');
        $this->load->view('payment/currency_list', $data);
        $this->load->view('fixed/footer');
    }

    public function add_currency()
    {
        if ($this->input->post()) {
            $code = $this->input->post('code', true);
            $symbol = $this->input->post('symbol', true);
            $spos = $this->input->post('spos');
            $rate = $this->input->post('rate');
            $decimal = $this->input->post('decimal');
            $thous_sep = $this->input->post('thous_sep');
            $deci_sep = $this->input->post('deci_sep');

            $this->billing->add_currency($code, $symbol, $spos, $rate, $decimal, $thous_sep, $deci_sep);

        } else {

            $head['title'] = $this->lang->line('Add Currency');
            $this->load->view('fixed/header', $head);
            $this->load->view('payment/add_currency');
            $this->load->view('fixed/footer');

        }

    }


    public function edit_currency()
    {
        if ($this->input->post()) {

            $gid = $this->input->post('gid');
            $code = $this->input->post('code', true);
            $symbol = $this->input->post('symbol', true);
            $spos = $this->input->post('spos');
            $rate = $this->input->post('rate');
            $decimal = $this->input->post('decimal');
            $thous_sep = $this->input->post('thous_sep');
            $deci_sep = $this->input->post('deci_sep');

            $this->billing->edit_currency($gid, $code, $symbol, $spos, $rate, $decimal, $thous_sep, $deci_sep);

        } else {

            $id = intval($this->input->get('id'));
            $head['title'] = $this->lang->line('Edit Currency');
            $data['currency_d'] = $this->invoices->currency_d($id);
            $this->load->view('fixed/header', $head);
            $this->load->view('payment/currency-edit', $data);
            $this->load->view('fixed/footer');

        }

    }

    public function delete_currency()
    {
        $id = $this->input->post('deleteid');
        if ($id) {
            $this->db->delete('geopos_currencies', array('id' => $id));
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }


    function exchange()
    {
        if ($this->input->post()) {

            $currency = $this->input->post('currency', true);
            $key1 = $this->input->post('key1', true);
            $key2 = $this->input->post('key2', true);
            $enable = $this->input->post('enable');
            $reverse = $this->input->post('reverse');


            $this->billing->exchange($currency, $key1, $key2, $enable, $reverse);

        } else {

            $this->load->model('plugins_model', 'plugins');
            $data['exchange'] = $this->plugins->universal_api(5);
            $this->load->view('fixed/header');
            $this->load->view('payment/exchange', $data);
            $this->load->view('fixed/footer');
        }
    }


}