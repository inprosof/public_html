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
require_once APPPATH . 'third_party/vendor/autoload.php';
require_once APPPATH . 'third_party/qrcode/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Endroid\QrCode\QrCode;

class Stockreturn extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Stockreturn_model', 'stockreturn');
        $this->load->library("Aauth");
		$this->load->library("Custom");
		$this->load->library("Common");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
    }
	
	public function duplicate()
	{
		$tid = $this->input->get('id');
		$typ = $this->input->get('typ');
		$this->create($tid, $typ);
	}
	
	
	public function convert()
	{
		$tid = $this->input->get('id');
		$typ = $this->input->get('typ');
		$this->create($tid, $typ);
	}
	
    //create invoice
    public function create($relation = 0, $typrelation = 0)
    {
        if (!$this->aauth->premission(2)) {
            exit($this->lang->line('translate19'));
        }
        $this->load->library("Common");
        $this->load->model('plugins_model', 'plugins');
		$this->load->model('settings_model', 'settings');
        $data['exchange'] = $this->plugins->universal_api(5);
        $data['currency'] = $this->stockreturn->currencies();
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
		
		///////////////////////////////////////////////////////////////////////
		////////////////////////Relação entre documentos//////////////////////
		$this->load->library("Related");
		$this->load->library("Transport");
		$typrelation = $this->input->post('typrelation');
		$relationid = $this->input->post('relationid');
		////////////////////////Relação entre documentos//////////////////////
		///////////////////////////////////////////////////////////////////////
        ////////////////////////Relação entre Permissões//////////////////////
		///////////////////////////////////////////////////////////////////////
		$data['autos'] = $this->common->guide_autos_company();
		if($this->aauth->get_user()->loc == 0)
		{
			$discship = $this->settings->online_pay_settings_main();
		}else{
			$discship = $this->settings->online_pay_settings($this->aauth->get_user()->loc);
		}
		
		$data['configs'] = $discship;
		$data['permissoes'] = $this->settings->permissions_loc($this->aauth->get_user()->loc);
		
        if ($discship['emps'] == 1) {
            $this->load->model('employee_model', 'employee');
            $data['employee'] = $this->employee->list_employee();
        }
		if ($this->aauth->get_user()->loc == 0 || $this->aauth->get_user()->loc == "0")
		{
			$data['locations'] = $this->settings->company_details(1);
		}else{
			$data['locations'] = $this->settings->company_details2($this->aauth->get_user()->loc);
		}
		
		$type = $this->input->get('ty');
		$data['tiprelated'] = 0;
		if($type > 0)
		{
			$data['tiprelated'] = 18;
		}else{
			$data['tiprelated'] = 6;
		}
		
		///////////////////////////////////////////////////////////////////////
		////////////////////////Relação entre documentos//////////////////////
		$data['typrelation'] = $typrelation;
		$data['relationid'] = $relation;
		
		if($relation > 0)
		{
			if($typrelation == 0){
				$data['tiprelated'] = 1;
			}
			$this->load->library("Related");
			$data['docs_origem'][] = $this->related->detailsAfterRelation($relation,$typrelation);
			$data['csd_name'] = $data['docs_origem']['name'];
			$data['csd_tax'] = $data['docs_origem']['taxid'];
			$data['csd_id'] = $data['docs_origem']['id'];
			$data['products'] = $this->related->detailsAfterRelationProducts($relation,$typrelation,0);
		}else{
			$data['csd_name'] = $this->lang->line('Default').": Consumidor Final";
			$data['csd_tax'] = "999999990";
			$data['csd_id'] = "99999999";
			$data['docs_origem'] = [];
			$data['products'] = [];
		}
		
		$typename = "";
		if($type == 1 || $type == '1')
		{
			$typename = "Retorno Stock Fornecedores ";
			if(count($this->settings->billingterms(18)) == 0)
			{
				exit('Deve Inserir pelo menos um Termo para o Tipo '.$typename.'. <a class="match-width match-height"  href="'.base_url().'settings/billing_terms"><i 
													class="ft-chevron-right"></i> Click aqui para o Fazer. </a> ');
			}
			$data['terms'] = $this->settings->billingterms(18);
		}else{
			$typename = "Retorno Stock Clientes ";
			if(count($this->settings->billingterms(6)) == 0)
			{
				exit('Deve Inserir pelo menos um Termo para o Tipo '.$typename.'. <a class="match-width match-height"  href="'.base_url().'settings/billing_terms"><i 
													class="ft-chevron-right"></i> Click aqui para o Fazer. </a> ');
			}
			$data['terms'] = $this->settings->billingterms(6);
		}
		$data['type_return'] = $type;
		$data['return']['type'] = $typename;
        $data['exchange'] = $this->plugins->universal_api(5);
		$data['withholdings'] = $this->settings->withholdings();
		$data['company'] = $this->settings->company_details(1);
        $data['customergrouplist'] = $this->customers->group_list();
        
        $data['warehouse'] = $this->stockreturn->warehouses();
        $data['currency'] = $this->stockreturn->currencies();
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$data['countrys'] = $this->common->countrys();
		$data['expeditions'] = $this->common->sexpeditions();
		$data['typesinvoices'] = "";
		
		$numget = 0;
		if($type == 1)
		{
			$this->li_a = 'suppliers';
			$data['typesinvoicesdefault'] = $this->common->default_typ_doc(18);
		}else{
			$this->li_a = 'crm';
			$data['typesinvoicesdefault'] = $this->common->default_typ_doc(6);
		}
		
		$data['seriesinvoiceselect'] = $this->common->default_series($this->aauth->get_user()->loc);
		if ($this->aauth->get_user()->loc == 0 || $this->aauth->get_user()->loc == "0")
		{
			$data['locations'] = $this->settings->company_details(1);
		}else{
			$data['locations'] = $this->settings->company_details2($this->aauth->get_user()->loc);
		}
		$data['taxdetails'] = $this->common->taxdetail();
		if($data['seriesinvoiceselect'] == NULL)
		{
			exit('Ainda não definiu nenhuma série para esta Localização. <a class="match-width match-height"  href="'.base_url().'settings/series"><i class="ft-chevron-right"></i> Click aqui para o Fazer. </a> ');
		}else{
			if($type == 1)
			{
				$seri_did_df = $this->common->default_series_id(18);
				$numget = $this->common->lastdoc(18,$seri_did_df);
			}else{
				$seri_did_df = $this->common->default_series_id(6);
				$numget = $this->common->lastdoc(6,$seri_did_df);
			}
			$data['lastinvoice'] = $numget;
			$head['title'] = 'Novo '.$typename;
			$head['usernm'] = $this->aauth->get_user()->username;
			$this->load->view('fixed/header', $head);
			if($type == 1)
			{
				$data['custom_fields'] = [];
				$data['c_custom_fields'] = $this->custom->add_fields(4);
				$this->load->view('stockreturn/newinvoice', $data);
			}else{
				$data['custom_fields'] = [];
				$data['c_custom_fields'] = $this->custom->add_fields(1);
				$this->load->view('stockreturn/c_newinvoice', $data);
				
			}
			$this->load->view('fixed/footer');
		}
    }

    //edit invoice
    public function edit()
    {
        if (!$this->aauth->premission(2)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $this->load->model('customers_model', 'customers');
		$this->load->model('settings_model', 'settings');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->settings->billingterms(10);
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
		
		///////////////////////////////////////////////////////////////////////
		////////////////////////Relação entre documentos//////////////////////
		$data['iddoc'] = $data['invoice']['id'];
		$data['csd_name'] = $data['invoice']['name'];
		$data['csd_tax'] = $data['invoice']['taxid'];
		$data['csd_id'] = $data['invoice']['id'];
		$data['relationid'] = $data['invoice']['factura_duplicada'];
		$data['tiprelated'] = 0;
		$this->load->library("Related");
		
		if($draf == 0){
			$data['docs_origem'] = $this->related->getRelated($tid,0,0);
			$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0);
			if($data['invoice']['i_class'] == 1)
			{
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,17,0);
			}else{
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,18,0);
			}
			
		}else{
			$data['docs_origem'] = $this->related->getRelated($tid,0,1);
			$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1);
			if($data['invoice']['i_class'] == 1)
			{
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,17,1);
			}else{
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,18,1);
			}
		}
		if($data['docs_deu_origem'] != null){
			if(count($data['docs_deu_origem']) > 0)
			{
				if($data['docs_deu_origem'][0] != null)
				{
					for($i = 0; $i < count($data['docs_deu_origem']); $i++)
					{
						if($data['docs_deu_origem'][$i]['tipologia'] == 1){
							$data['tiprelated'] = $data['docs_deu_origem'][$i]['doc'];
							break;
						}
					}
				}
			}
		}
		$data['iddoc'] = $data['invoice']['id'];
		$data['csd_name'] = $data['invoice']['name'];
		$data['csd_tax'] = $data['invoice']['taxid'];
		$data['csd_id'] = $data['invoice']['id'];
		////////////////////////Relação entre documentos//////////////////////
		///////////////////////////////////////////////////////////////////////
		$data['custom_fields'] = [];
		if($data['invoice']['i_class'] == 2)
		{
			$data['terms'] = $this->settings->billingterms(18);
			$data['c_custom_fields'] = $this->custom->view_edit_fields($data['invoice']['cid'], 4);
		}else{
			$data['terms'] = $this->settings->billingterms(6);
			$data['c_custom_fields'] = $this->custom->view_edit_fields($data['invoice']['cid'], 1);
		}
		if($this->aauth->get_user()->loc == 0)
		{
			$discship = $this->settings->online_pay_settings_main();
		}else{
			$discship = $this->settings->online_pay_settings($this->aauth->get_user()->loc);
		}
		$data['configs'] = $discship;
		$data['permissoes'] = $this->settings->permissions_loc($this->aauth->get_user()->loc);
		
        if ($discship['emps'] == 1) {
            $this->load->model('employee_model', 'employee');
            $data['employee'] = $this->employee->list_employee();
        }
		
		$data['typesinvoices'] = [];
		$data['typesinvoicesdefault'] = $this->common->default_typ_doc($data['invoice']['irs_type']);
		$data['seriesinvoiceselect'] = $this->common->default_series($this->aauth->get_user()->loc);
		if ($this->aauth->get_user()->loc == 0 || $this->aauth->get_user()->loc == "0")
		{
			$data['locations'] = $this->settings->company_details(1);
		}else{
			$data['locations'] = $this->settings->company_details2($this->aauth->get_user()->loc);
		}
		
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/edit', $data);
        $this->load->view('fixed/footer');
    }

    //invoices list
    public function index()
    {
        if (!$this->aauth->premission(2)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
		
		$type = $this->input->get('ty');
		$typename = "";
		$head['usernm'] = $this->aauth->get_user()->username;
        
		if($type == 1 || $type == '1')
		{
			$this->li_a = 'suppliers';
			$head['title'] = "Gestão Notas de Devolução de Fornecedores";
			$typename = "Notas de Devolução de Fornecedores ";
		}else{
			$this->li_a = 'crm';
			$head['title'] = "Gestão Notas de Devolução de Clientes";
			$typename = "Notas de Devolução de Clientes ";
		}
        $this->load->view('fixed/header', $head);
		if($type == 1 || $type == '1')
		{
			$this->load->view('stockreturn/invoices');
		}else{
			$this->load->view('stockreturn/invoices_client');
		}
        $this->load->view('fixed/footer');
    }

    public function action2()
    {
		$this->action(0);
	}
    public function action($typp=1,$vers=0,$idgu=0)
    {
        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');
        $new_u = 'create';
        if ($person_type) {
            $new_u = 'create_client';
            if (!$this->aauth->premission(2)) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            $new_u = 'create_note';
            if (!$this->aauth->premission(1)) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
		$warehouse = $this->input->post('s_warehouses', true);
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
                "Please add a new person or search from a previous added!"));
            exit;
        }
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        if (!$currency) $currency = 0;
        $data = array('tid' => $invocieno, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'loc' => $warehouse, 'i_class' => $person_type, 'multi' => $currency,);
        if ($this->db->insert('geopos_stock_r', $data)) {
            $invocieno = $this->db->insert_id();
            $pid = $this->input->post('pid');
            $productlist = array();
            $prodindex = 0;
            $itc = 0;
            $product_id = $this->input->post('pid');
            $product_name1 = $this->input->post('product_name', true);
            $product_qty = $this->input->post('product_qty');
            $product_price = $this->input->post('product_price');
            $product_tax = $this->input->post('product_tax');
            $product_discount = $this->input->post('product_discount');
            $product_subtotal = $this->input->post('product_subtotal');
            $ptotal_tax = $this->input->post('taxa');
            $ptotal_disc = $this->input->post('disca');

            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn');
            foreach ($pid as $key => $value) {
                $total_discount += numberClean(@$ptotal_disc[$key]);
                $total_tax += numberClean($ptotal_tax[$key]);
                $data = array(
                    'tid' => $invocieno,
                    'pid' => $product_id[$key],
                    'product' => $product_name1[$key],
                    'code' => $product_hsn[$key],
                    'qty' => numberClean($product_qty[$key]),
                    'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                    'tax' => numberClean($product_tax[$key]),
                    'discount' => numberClean($product_discount[$key]),
                    'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                    'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                    'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                    'product_des' => $product_des[$key],
                    'unit' => $product_unit[$key]
                );
                $productlist[$prodindex] = $data;
                $i++;
                $prodindex++;
                $amt = numberClean($product_qty[$key]);
                if ($product_id[$key] > 0) {
                    if ($this->input->post('update_stock') == 'yes') {

                        if ($person_type) {
                            $this->db->set('qty', "qty+$amt", FALSE);
                        } else {
                            $this->db->set('qty', "qty-$amt", FALSE);
                        }

                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                    }
                    $itc += $amt;
                }
            }

            if ($prodindex > 0) {
                $this->db->insert_batch('geopos_stock_r_items', $productlist);
                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocieno);
                $this->db->update('geopos_stock_r');
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                    "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }

            echo json_encode(array('status' => 'Success', 'message' =>"Documento Criado com Sucesso. <a href='view?id=$invocieno&draf=0' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>   <a href='" . $new_u . "' class='btn btn-pink btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }
    }

    public function ajax_list()
    {
        $no = $this->input->post('start');
        $type = $this->input->get('t');
        $list = $this->stockreturn->get_datatables($type);
        $data = array();
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = '<a href="' . base_url("stockreturn/view?id=$invoices->id") . '">&nbsp; ' . $invoices->tid . '</a>';
            $row[] = $invoices->name;
            $row[] = dateformat($invoices->invoicedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="badge st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
            $row[] = '<a href="' . base_url("stockreturn/view?id=$invoices->id") . '" class="btn btn-success btn-sm"><i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a> &nbsp; <a href="' . base_url("stockreturn/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a>&nbsp;<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->stockreturn->count_all(),
            "recordsFiltered" => $this->stockreturn->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function view()
    {
        $this->load->model('accounts_model');
		$this->load->library("Common");
		$data['metodos_pagamentos'] = $this->common->smetopagamento();
        $data['acclist'] = $this->accounts_model->accountslist();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);
        $data['activity'] = $this->stockreturn->purchase_transactions($tid);
        $data['attach'] = $this->stockreturn->attach($tid);
        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
		
		$data['c_custom_fields'] = [];
		$data['custom_fields'] = [];
        if (CUSTOM) 
			if($data['invoice']['ext'] == 0)
			{
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
			}else{
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 4, 1);
			}
			
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        if (($data['invoice']['i_class'] != 2 && $this->aauth->premission(2)) or ($data['invoice']['i_class'] == 2 && $this->aauth->premission(1))) {
            $this->load->view('fixed/header', $head);
            if ($data['invoice']['tid']) $this->load->view('stockreturn/view', $data);
            $this->load->view('fixed/footer');
        }

    }


    public function printinvoice()
    {
        $tid = $this->input->get('id');
        $ty = $this->input->get('ty');
        $data['id'] = $tid;
        $data['title'] = $this->lang->line('Stock Return')." $tid";
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);
        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        if (($data['invoice']['i_class'] != 2 && $this->aauth->premission(2)) or ($data['invoice']['i_class'] == 2 && $this->aauth->premission(1))) {
            if ($ty < 2) {
                $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
            } else {
                $data['general'] = array('title' => $this->lang->line('Credit Note'), 'person' => $this->lang->line('Customer'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
            }
			
			$token = $this->input->get('token');
			$data['qrc'] = 'stk_ret_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			$qrCode = new QrCode(base_url('stockreturn/printinvoice?id=' . $tid . '&d=1&token=' . $token));
			$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$data['Tipodoc'] = "Original";
			
            ini_set('memory_limit', '64M');
            if ($data['invoice']['taxstatus'] == 'cgst' || $data['invoice']['taxstatus'] == 'igst') {
                $html = $this->load->view('print_files/invoice-a4-gst_v' . INVV, $data, true);
            } else {
                $html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
            }
            //PDF Rendering
            $this->load->library('pdf');
            if (INVV == 1) {
                $header = $this->load->view('print_files/invoice-header_v' . INVV, $data, true);
                $pdf = $this->pdf->load_split(array('margin_top' => 40));
                $pdf->SetHTMLHeader($header);
            }
            if (INVV == 2) {
                $pdf = $this->pdf->load_split(array('margin_top' => 5));
            }
            $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');
            $pdf->WriteHTML($html);
            $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'stock_return__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
            if ($this->input->get('d')) {
                $pdf->Output($file_name . '.pdf', 'D');
            } else {
                $pdf->Output($file_name . '.pdf', 'I');
            }
        }
    }

    public function delete_i()
    {
        $id = $this->input->post('deleteid');
        if ($this->stockreturn->purchase_delete($id, $this->limited)) {
            echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
        }
    }

    public function editaction()
    {
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');
        if ($person_type) {
            if (!$this->aauth->premission(2)) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            if (!$this->aauth->premission(1)) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('iid');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
                "Please add a new supplier or search from a previous added!"));
            exit;
        }
        $currency = $this->input->post('mcurrency');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $this->db->trans_start();
        $flag = false;
        $transok = true;
        //Product Data
        $pid = $this->input->post('pid');
        $productlist = array();
        $prodindex = 0;
        $this->db->delete('geopos_stock_r_items', array('tid' => $invocieno));
        $product_id = $this->input->post('pid');
        $product_name1 = $this->input->post('product_name', true);
        $product_qty = $this->input->post('product_qty');
        $old_product_qty = $this->input->post('old_product_qty');
        if ($old_product_qty == '') $old_product_qty = 0;
        $product_price = $this->input->post('product_price');
        $product_tax = $this->input->post('product_tax');
        $product_discount = $this->input->post('product_discount');
        $product_subtotal = $this->input->post('product_subtotal');
        $ptotal_tax = $this->input->post('taxa');
        $ptotal_disc = $this->input->post('disca');
        $product_des = $this->input->post('product_description', true);
        $product_unit = $this->input->post('unit');
        $product_hsn = $this->input->post('hsn');
        foreach ($pid as $key => $value) {
            $total_discount += numberClean(@$ptotal_disc[$key]);
            $total_tax += numberClean($ptotal_tax[$key]);
            $data = array(
                'tid' => $invocieno,
                'pid' => $product_id[$key],
                'product' => $product_name1[$key],
                'code' => $product_hsn[$key],
                'qty' => numberClean($product_qty[$key]),
                'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                'tax' => numberClean($product_tax[$key]),
                'discount' => numberClean($product_discount[$key]),
                'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                'product_des' => $product_des[$key],
                'unit' => $product_unit[$key]
            );
            $productlist[$prodindex] = $data;
            $i++;
            $prodindex++;
            if ($this->input->post('update_stock') == 'yes') {
                $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
                $this->db->set('qty', "qty-$amt", FALSE);
                $this->db->where('pid', $product_id[$key]);
                $this->db->update('geopos_products');
            }
            $flag = true;
        }
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $data = array('invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'items' => $i, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'i_class' => $person_type);
        $this->db->set($data);
        $this->db->where('id', $invocieno);
        if ($flag) {
            if ($this->db->update('geopos_stock_r', $data)) {
                $this->db->insert_batch('geopos_stock_r_items', $productlist);
                echo json_encode(array('status' => 'Success', 'message' =>
                    "Updated! <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> View </a> "));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                    "There is a missing field!"));
                $transok = false;
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                "Please add atleast one product in order!"));
            $transok = false;
        }

        if ($this->input->post('update_stock') == 'yes') {
            if ($this->input->post('restock')) {
                foreach ($this->input->post('restock') as $key => $value) {
                    $myArray = explode('-', $value);
                    $prid = $myArray[0];
                    $dqty = numberClean($myArray[1]);
                    if ($prid > 0) {
                        $this->db->set('qty', "qty-$dqty", FALSE);
                        $this->db->where('pid', $prid);
                        $this->db->update('geopos_products');
                    }
                }
            }
        }
        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }
    }

    public function update_status()
    {
        $tid = $this->input->post('tid');
        $status = $this->input->post('status');
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->premission(2)) or ($stock['i_class'] == 2 && $this->aauth->premission(1))) {
            $this->db->set('status', $status);
            $this->db->where('id', $tid);
            $this->db->update('geopos_stock_r');
            echo json_encode(array('status' => 'Success', 'message' =>
                'Status updated successfully!', 'pstatus' => $status));
        }
    }

    public function file_handling()
    {
        if ($this->input->get('op')) {
            $name = $this->input->get('name');
            $invoice = $this->input->get('invoice');
            if ($this->stockreturn->meta_delete($invoice, 5, $name)) {
                echo json_encode(array('status' => 'Success'));
            }
        } else {
            $id = $this->input->get('id');
            $this->load->library("Uploadhandler_generic", array(
                'accept_file_types' => '/\.(gif|jpe?g|png|docx|docs|txt|pdf|xls)$/i', 'upload_dir' => FCPATH . 'userfiles/attach/', 'upload_url' => base_url() . 'userfiles/attach/'
            ));
            $files = (string)$this->uploadhandler_generic->filenaam();
            if ($files != '') {
                $this->stockreturn->meta_insert($id, 5, $files);
            }
        }
    }

    public function cancelorder()
    {
        $tid = intval($this->input->post('tid'));
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->premission(2)) or ($stock['i_class'] == 2 && $this->aauth->premission(1))) {
            $this->db->set('pamnt', "0.00", FALSE);
            $this->db->set('status', 'canceled');
            $this->db->where('id', $tid);
            $this->db->update('geopos_stock_r');
            //reverse
            $this->db->select('credit,acid');
            $this->db->from('geopos_transactions');
            $this->db->where('tid', $tid);
            $this->db->where('ext', 6);
            $query = $this->db->get();
            $revresult = $query->result_array();
            foreach ($revresult as $trans) {
                $amt = $trans['credit'];
                $this->db->set('lastbal', "lastbal-$amt", FALSE);
                $this->db->where('id', $trans['acid']);
                $this->db->update('geopos_accounts');
            }
            $this->db->select('pid,qty');
            $this->db->from('geopos_stock_r_items');
            $this->db->where('tid', $tid);
            $query = $this->db->get();
            $prevresult = $query->result_array();
            foreach ($prevresult as $prd) {
                $amt = $prd['qty'];
                $this->db->set('qty', "qty+$amt", FALSE);
                $this->db->where('pid', $prd['pid']);
                $this->db->update('geopos_products');
            }
            $this->db->delete('geopos_transactions', array('tid' => $tid, 'ext' => 6));
            echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('Return canceled')));

        }
    }


    public function pay()
    {
        $this->load->library("Custom");
        $tid = intval($this->input->post('tid'));
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->premission(2)) or ($stock['i_class'] == 2 && $this->aauth->premission(1))) {

            $amount = rev_amountExchange_s($this->input->post('amount', true), 0, $this->aauth->get_user()->loc);
            $paydate = $this->input->post('paydate');
            $note = $this->input->post('shortnote', true);
            $pmethod = $this->input->post('pmethod');
            $acid = $this->input->post('account');
            $cid = $this->input->post('cid');
            $cname = $this->input->post('cname', true);
            $paydate = datefordatabase($paydate);


            if ($stock['i_class'] == 2 or $stock['i_class'] == 1) {
                $this->db->select('holder');
                $this->db->from('geopos_accounts');
                $this->db->where('id', $acid);
                $query = $this->db->get();
                $account = $query->row_array();

                $data = array(
                    'acid' => $acid,
                    'account' => $account['holder'],
                    'type' => 'Expense',
                    'cat' => 'Credit Note',
                    'debit' => $amount,
                    'payer' => $cname,
                    'payerid' => $cid,
                    'method' => $pmethod,
                    'date' => $paydate,
                    'eid' => $this->aauth->get_user()->id,
                    'tid' => $tid,
                    'note' => $note,
                    'ext' => 6
                );
                $this->db->insert('geopos_transactions', $data);
                $this->db->insert_id();
                $this->db->select('total,csd,pamnt');
                $this->db->from('geopos_stock_r');
                $this->db->where('id', $tid);
                $query = $this->db->get();
                $invresult = $query->row();
                $totalrm = $invresult->total - $invresult->pamnt;
                if ($totalrm > $amount) {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'partial');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //account update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $paid_amount = $invresult->pamnt + $amount;
                    $status = 'Partial';
                    $totalrm = $totalrm - $amount;
                } else {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'accepted');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //acount update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $totalrm = 0;
                    $status = 'Accepted';
                    $paid_amount = $amount;
                }
				$this->load->model('settings_model', 'settings');
				$discship = [];
				if($this->aauth->get_user()->loc == 0)
				{
					$discship = $this->settings->online_pay_settings_main();
				}else{
					$discship = $this->settings->online_pay_settings($this->aauth->get_user()->loc);
				}
				$dual = $discship;
                if($dual['dual_entry']>0){
                    $this->db->select('holder');
                    $this->db->from('geopos_accounts');
                    $this->db->where('id', $dual['ac_id_d']);
                    $query = $this->db->get();
                    $account = $query->row_array();

                    $data['debit'] = 0;
                    $data['credit'] = $amount;
                    $data['type'] = 'Income';
                    $data['acid'] = $dual['ac_id_d'];
                    $data['account'] = $account['holder'];
                    $data['note'] = 'Credit ' . $data['note'];

                    $this->db->insert('geopos_transactions', $data);

                    //account update
                    $this->db->set('lastbal', "lastbal+$amount", FALSE);
                    $this->db->where('id', $dual['ac_id_d']);
                    $this->db->update('geopos_accounts');
                }
            } else {


                $this->db->select('holder');
                $this->db->from('geopos_accounts');
                $this->db->where('id', $acid);
                $query = $this->db->get();
                $account = $query->row_array();

                $data = array(
                    'acid' => $acid,
                    'account' => $account['holder'],
                    'type' => 'Income',
                    'cat' => 'Purchase',
                    'credit' => $amount,
                    'payer' => $cname,
                    'payerid' => $cid,
                    'method' => $pmethod,
                    'date' => $paydate,
                    'eid' => $this->aauth->get_user()->id,
                    'tid' => $tid,
                    'note' => $note,
                    'ext' => 6
                );
                $this->db->insert('geopos_transactions', $data);
                $this->db->insert_id();
                $this->db->select('total,csd,pamnt');
                $this->db->from('geopos_stock_r');
                $this->db->where('id', $tid);
                $query = $this->db->get();
                $invresult = $query->row();
                $totalrm = $invresult->total - $invresult->pamnt;
                if ($totalrm > $amount) {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'partial');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //account update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $paid_amount = $invresult->pamnt + $amount;
                    $status = 'Partial';
                    $totalrm = $totalrm - $amount;
                } else {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'accepted');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //acount update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $totalrm = 0;
                    $status = 'Accepted';
                    $paid_amount = $amount;
                }
            }


            $activitym = "<tr><td>" . substr($paydate, 0, 10) . "</td><td>$pmethod</td><td>$amount</td><td>$note</td></tr>";
            echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('Transaction has been added'), 'pstatus' => $this->lang->line($status), 'activity' => $activitym, 'amt' => $totalrm, 'ttlpaid' => $paid_amount));

        }
    }
}