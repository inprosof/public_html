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

use Omnipay\Omnipay;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Billing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('csrf_protection', FALSE);
        $this->load->model('invoices_model', 'invocies');
		$this->load->model('invoices_supli_model', 'invociessupli');
        $this->load->model('billing_model', 'billing');
        $this->load->library("Aauth");
        $this->load->library("Custom");
		if ($this->aauth->get_user()->roleid == 5 || $this->aauth->get_user()->roleid == 7 || $this->aauth->premission(128)) {
            $this->limited = '';
        } else {
			$this->limited = $this->aauth->get_user()->id;
        }
    }
	
	public function viewsupli()
    {
        if (!$this->input->get()) {
            exit();
        }

        $tid = $this->input->get('id');
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {

            $this->load->model('accounts_model');
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->invociessupli->invoice_details($tid, '', false);
            $data['acclist'] = $this->accounts_model->accountslist(false . $data['invoice']['loc']);
            $data['online_pay'] = $this->billing->online_pay_settings();
            $data['products'] = $this->invociessupli->invoice_products($tid);
            $data['activity'] = $this->invociessupli->invoice_transactions($tid);
            $data['attach'] = $this->invociessupli->attach($tid);
            if (CUSTOM) 
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 4, 1);
            $data['gateway'] = $this->billing->gateway_list('Yes');
            $data['employee'] = $this->invociessupli->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $head['title'] = "Invoice " . $data['invoice']['tid'];
            $this->load->view('billing/header', $head);
            $this->load->view('billing/view', $data);
            $this->load->view('billing/footer');
        }
    }
	
	public function viewreceipts()
    {
        if (!$this->input->get()) {
            exit();
        }

        $tid = $this->input->get('id');
		$draf = $this->input->get('draf');
		$temp = $this->input->get('temp');
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('accounts_model');
			$this->load->model('receipts_model', 'receipts');
            $data['id'] = $tid;
            $data['token'] = $token;
			$data['draf'] = $draf;
			$data['temp'] = $temp;
            $data['invoice'] = $this->receipts->invoice_details($tid, $this->limited,true,0,$draf);
            $data['acclist'] = $this->accounts_model->accountslist(false . $data['invoice']['loc']);
            $data['online_pay'] = $this->billing->online_pay_settings();
			///////////////////////////////////////////////////////////////////////
			////////////////////////Relação entre documentos//////////////////////
			$data['products'] = [];
			$this->load->library("Related");
			$data['relationid'] = $data['invoice']['factura_duplicada'];
			$data['tiprelated'] = 0;
			
			$typerelatset = 0;
			if($data['invoice']['irs_type'] == 23)
			{
				$typerelatset = 14;
			}else if($data['invoice']['irs_type'] == 27)
			{
				$typerelatset = 113;
			}else if($data['invoice']['irs_type'] == 28)
			{
				$typerelatset = 114;
			}else{
				$typerelatset = 13;
			}
			
			if($draf == 0){
				$data['docs_origem'] = $this->related->getRelated($tid,0,0,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0,0,$typerelatset);
			}else{
				$data['docs_origem'] = $this->related->getRelated($tid,0,1,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1,0,$typerelatset);
			}
			$data['activity'] = $this->receipts->invoice_transactions($tid,0);
            $data['attach'] = $this->receipts->attach($tid);
			$data['c_custom_fields'] = [];
			$data['custom_fields'] = [];
			if (CUSTOM) 
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);			
			
            $data['gateway'] = $this->billing->gateway_list('Yes');
            $data['employee'] = $this->receipts->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $data['invoice']['irs_type_s'] . '_' . $data['invoice']['tid']);
            $head['title'] = $file_name;
            $this->load->view('billing/header', $head);
            $this->load->view('billing/receipts', $data);
            $this->load->view('billing/footer');
        }
    }

    public function view()
    {
        if (!$this->input->get()) {
            exit();
        }

        $tid = $this->input->get('id');
		$draf = $this->input->get('draf');
		$temp = $this->input->get('temp');
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('accounts_model');
            $data['id'] = $tid;
            $data['token'] = $token;
			$data['draf'] = $draf;
			$data['temp'] = $temp;
            $data['invoice'] = $this->invocies->invoice_details($tid, '', false);
            $data['acclist'] = $this->accounts_model->accountslist(false . $data['invoice']['loc']);
            $data['online_pay'] = $this->billing->online_pay_settings();
            $data['products'] = $this->invocies->invoice_products($tid);
            $data['activity'] = $this->invocies->invoice_transactions($tid);
            $data['attach'] = $this->invocies->attach($tid);
            $data['c_custom_fields'] = [];
			$data['custom_fields'] = [];
			if (CUSTOM) 
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
				$data['custom_fields'] = $this->custom->view_fields_data($tid, 2);
            $data['gateway'] = $this->billing->gateway_list('Yes');
            $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $data['invoice']['irs_type_s'] . '_' . $data['invoice']['tid']);
            $head['title'] = $file_name;
            $this->load->view('billing/header', $head);
            $this->load->view('billing/view', $data);
            $this->load->view('billing/footer');
        }

    }
	
	
	public function viewwhat()
    {
		if (!$this->input->get()) {
            exit();
        }

        $invoice = $this->input->get('invoice');
		$invoice = base64_decode($invoice);
		
		$myArrayVals = explode("&", $invoice);
		
		
		$tid = $myArrayVals[0];
		$draf = $myArrayVals[1];
		$temp = $myArrayVals[2];
        $token = $myArrayVals[3];
		
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('accounts_model');
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->invocies->invoice_details($tid, '', false);
            $data['acclist'] = $this->accounts_model->accountslist(false . $data['invoice']['loc']);
            $data['online_pay'] = $this->billing->online_pay_settings();
            $data['products'] = $this->invocies->invoice_products($tid);
            $data['activity'] = $this->invocies->invoice_transactions($tid);
            $data['attach'] = $this->invocies->attach($tid);
            if (CUSTOM) 
				$data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
            $data['gateway'] = $this->billing->gateway_list('Yes');
            $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
            $head['usernm'] = '';
			$file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $data['invoice']['irs_type_s'] . '_' . $data['invoice']['tid']);
            $head['title'] = $file_name;
            $this->load->view('billing/header', $head);
            $this->load->view('billing/view', $data);
            $this->load->view('billing/footer');
        }
	}
	


    public function quoteview()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 'q' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('quote_model', 'quote');
			$this->load->library("Common");
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->quote->quote_details($tid);
			$data['products'] = $this->quote->quote_products($tid);
            $data['attach'] = $this->quote->attach($tid);
            $data['employee'] = $this->quote->employee($data['invoice']['eid']);
            $head['title'] = "Orçamento Nº" . $data['invoice']['tid'];
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/quoteview', $data);
            $this->load->view('billing/footer');
        }

    }
	
	public function docinview()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
		$ty = intval($this->input->get('ty'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 'q' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('docs_intern_model', 'docs');
			$this->load->library("Common");
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->docs->docs_details($tid);
			$data['products'] = $this->docs->docs_products($tid);
            $data['attach'] = $this->docs->attach($tid);
            $data['employee'] = $this->docs->employee($data['invoice']['eid']);
			if($data['invoice']['ext'] == 0){
				$head['title'] = "Documento Interno Nº" . $data['invoice']['tid'];
			}else{
				$head['title'] = "Documento Interno Fornecedor Nº" . $data['invoice']['tid'];
			}
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/quoteview', $data);
            $this->load->view('billing/footer');
        }

    }

    public function purchase()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 'p' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('purchase_model', 'purchase');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $data['attach'] = $this->purchase->attach($tid);
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->purchase->purchase_details($tid);
            // $data['online_pay'] = $this->purchase->online_pay_settings();
            $data['products'] = $this->purchase->purchase_products($tid);
            $data['activity'] = $this->purchase->purchase_transactions($tid);
            $head['title'] = "Purchase " . $data['invoice']['tid'];
            $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/purchase', $data);
            $this->load->view('billing/footer');
        }
    }

    public function stockreturn()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $data['attach'] = $this->stockreturn->attach($tid);
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->stockreturn->purchase_details($tid);
            // $data['online_pay'] = $this->purchase->online_pay_settings();
            $data['products'] = $this->stockreturn->purchase_products($tid);
            $data['activity'] = $this->stockreturn->purchase_transactions($tid);
            $head['title'] = "Order " . $data['invoice']['tid'];
            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/stockreturn', $data);
            $this->load->view('billing/footer');
        }
    }


    public function gateway()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->post('tid'));
        $token = $this->input->post('token');
        $amount = $this->input->post('p_amount');
        $pay_gateway = $this->input->post('pay_gateway');
		
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            switch ($pay_gateway) {
                case 1 :
                    $this->card();
                    break;
            }
        }
    }
	
	
	public function printinvoicesupli()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $data['id'] = $tid;
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
            $data['invoice'] = $this->invociessupli->invoice_details($tid);
            $data['title'] = $data['invoice']['irs_type_s'].' - '.$data['invoice']['irs_type_n'];
            $data['products'] = $this->invociessupli->invoice_products($tid);
            $data['employee'] = $this->invociessupli->employee($data['invoice']['eid']);
			$data['activity'] = $this->invociessupli->invoice_transactions($tid);
            if (CUSTOM) {
                $data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
                $data['i_custom_fields'] = $this->custom->view_fields_data($tid, 2, 1);
            }
			
			$data['invoice']['type'] = $data['invoice']['irs_type_n'];
			$this->load->model('billing_model', 'billing');
			$online_pay = $this->billing->online_pay_settings();
			if ($online_pay['enable'] == 1) {
			}
			
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			
			
			$codQRD = 'A:'.$data['invoice']['loc_country'].$data['invoice']['loc_taxid'].'*';
			$codQRD .= 'B:'.$data['invoice']['taxid'].'*';
			$codQRD .= 'C:'.$data['invoice']['country'].'*';
			$codQRD .= 'D:'.$data['invoice']['irs_type_s'].'*';
			
			//“N” – Normal; “S” – Autofaturação; “A” – Documento anulado; “R” – Documento de resumo doutros documentos criados noutras aplicações e gerado nesta aplicação; “F” – Documento faturado
			
			if($data['invoice']['status'] == 'canceled')
			{
				$codQRD .= 'E:A*';
			}else{
				$codQRD .= 'E:N*';
			}
			
			$date = new DateTime($data['invoice']['invoicedate']);
			//$date = $date->format('Y-m-dTH:i:s');
			$date = $date->format('Ymd');
			
			$codQRD .= 'F:'.$date.'*';
			$codQRD .= 'G:'.$data['invoice']['irs_type_s'].$data['invoice']['serie_name'].'/'.$data['invoice']['tid'].'*';
			if($data['invoice']['atc_serie'] == "")
			{
				$codQRD .= 'H:0*';
			}else{
				$codQRD .= 'H:'.$data['invoice']['atc_serie'].$data['invoice']['tid'].'*';
			}
			
			$arrtudo = [];
			foreach ($data['products'] as $row) {
				$myArraytaxname = explode(";", $row['taxaname']);
				$myArraytaxcod = explode(";", $row['taxacod']);
				$myArraytaxvals = explode(";", $row['taxavals']);
				$myArraytaxcomo = explode(";", $row['taxacomo']);
				$myArraytaxperc = explode(";", $row['taxaperc']);
				for($i = 0; $i < count($myArraytaxname); $i++)
				{
					$jatem = false;
					for($oo = 0; $oo < count($arrtudo); $oo++)
					{
						if($arrtudo[$oo]['title'] == $myArraytaxname[$i])
						{
							$arrtudo[$oo]['total'] = ($arrtudo[$oo]['total']+$myArraytaxvals[$i]);
							$jatem = true;
							break;
						}
					}
					
					if(!$jatem)
					{
						$stack = array('title'=>$myArraytaxname[$i], 'total'=>$myArraytaxvals[$i], 'base'=>$myArraytaxvals[$i], 'cod'=>$myArraytaxcod[$i], 'como'=>$myArraytaxcomo[$i], 'perc'=>$myArraytaxperc[$i]);
						array_push($arrtudo, $stack);
					}
				}
			}
			
			//“PT-AC” – Espaço fiscal da Região Autónoma dos Açores; e “PT-MA” – Espaço fiscal da Região Autónoma da Madeira
			if($data['invoice']['loc_zon_fis'] == 0)
			{
				$codQRD .= 'J1:PT-AC*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'J2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'J3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'J5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'J7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else if($data['invoice']['loc_zon_fis'] == 1)
			{
				$codQRD .= 'K1:PT-MA*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'K2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'K3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'K5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'K7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else{
				$codQRD .= 'I1:'.$data['invoice']['loc_country'].'*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'I2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'I3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'I5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'I7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}

			
			
			
			//$codQRD .= 'L:'.'*';
			//$codQRD .= 'M:'.'*';
			
			$codQRD .= 'N:'.@number_format($data['invoice']['tax'], 2, '.', '').'*';
			$codQRD .= 'O:'.@number_format($data['invoice']['total'], 2, '.', '').'*';
			
			//Valor do total das retenções na fonte - campo WithholdingTaxAmount do SAF-T (PT).
			//$codQRD .= 'P:'.'*';
			
			
			//4 carateres do Hash gerados na criação do documento e buscar a AT
			$codQRD .= 'Q:'.'*';
			
			
			$codQRD .= 'R:'.$data['invoice']['loc_certification'].'*';
			
			$campfim = "";
			if($data['invoice']['loc_contabancaria'] != null)
			{
				$campfim .= "IBAN-".$data['invoice']['loc_contabancaria'];
			}
			
			$campfim .= ";".$data['invoice']['loc_cname'];
			$codQRD .= 'S:'.$campfim.'*';
			
			$qrCode = new QrCode($codQRD);
			//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$writer = new PngWriter();
			$writer->write($qrCode)->saveToFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			ini_set('memory_limit', '64M');
		
            $data['round_off'] = $this->custom->api_config(4);
			$pref = $data['invoice']['irs_type_n'];
            $data['general'] = array('title' => $data['invoice']['irs_type_s'].' - '.$data['invoice']['irs_type_n'], 'person' => $this->lang->line('Supplier'), 'prefix' => $pref, 't_type' => 0);
			
			$data['Tipodoc'] = "Original";
			$data2 = $data;
			$data2['Tipodoc'] = "Duplicado";

            //But the first serialized its there the data....
            $html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
			
            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_split(array('margin_top' => 10));
            $loc2 = location(0);
			$pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">Processado por Programa Certificado nº'.$loc2['certification'].' {PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');
            $pdf->WriteHTML($html);
			$pdf->AddPage();
			$html2 = $this->load->view('print_files/invoice-a4_v' . INVV, $data2, true);
			$pdf->WriteHTML($html2);
            if ($this->input->get('d')) {
                $pdf->Output($data['invoice']['irs_type_s'] . $data['invoice']['tid'] . '.pdf', 'D');
            } else {
                $pdf->Output($data['invoice']['irs_type_s'] . $data['invoice']['tid'] . '.pdf', 'I');
            }
        }
    }


    public function printinvoice()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
		$draf = intval($this->input->get('draf'));
		$temp = intval($this->input->get('temp'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $data['id'] = $tid;
			if($draf == 0 || $draf == ''){
				$data['typeinvoice'] = 'Invoice';
				$data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
				$data['title'] = $data['invoice']['irs_type_s'].' - '.$data['invoice']['irs_type_n'];
				$data['activity'] = $this->invocies->invoice_transactions($tid);
				if($data['invoice']['status'] == 'canceled')
				{
					$data['ImageBackGround'] = 'assets/images/anulada.png';
				}
			}else{
				$data['invoice'] = $this->invocies->invoice_details2($tid, $this->limited);
				$data['title'] = 'Rascunho '.$data['invoice']['irs_type_s'].' - '.$data['invoice']['irs_type_n'];
				$data['activity'] = $this->invocies->invoice_transactions2($tid);
				$data['typeinvoice'] = 'Rascunho';
				$data['ImageBackGround'] = 'assets/images/rascunho.png';
			}
			
			
			///////////////////////////////////////////////////////////////////////
			////////////////////////Relação entre documentos//////////////////////
			$data['relationid'] = $data['invoice']['factura_duplicada'];
			$data['tiprelated'] = 0;
			$this->load->library("Related");
			$typerelatset = 0;
			if($data['invoice']['irs_type'] == 1){
				$typerelatset = 0;
			}else if($data['invoice']['irs_type'] == 2){
				$typerelatset = 100;
			}else if($data['invoice']['irs_type'] == 3){
				$typerelatset = 200;
			}else if($data['invoice']['irs_type'] == 5){
				$typerelatset = 6;
			}else if($data['invoice']['irs_type'] == 6){
				$typerelatset = 17;
			}else if($data['invoice']['irs_type'] == 7){
				$typerelatset = 5;
			}else if($data['invoice']['irs_type'] == 8){
				$typerelatset = 3;
			}else if($data['invoice']['irs_type'] == 9){
				$typerelatset = 15;
			}else if($data['invoice']['irs_type'] == 10){
				$typerelatset = 12;
			}else if($data['invoice']['irs_type'] == 12){
				$typerelatset = 8;
			}else if($data['invoice']['irs_type'] == 13){
				$typerelatset = 9;
			}else if($data['invoice']['irs_type'] == 14){
				$typerelatset = 4;
			}else if($data['invoice']['irs_type'] == 15){
				$typerelatset = 2;
			}else if($data['invoice']['irs_type'] == 17){
				$typerelatset = 7;
			}else if($data['invoice']['irs_type'] == 22){
				$typerelatset = 13;
			}else if($data['invoice']['irs_type'] == 25){
				$typerelatset = 111;
			}else if($data['invoice']['irs_type'] == 26){
				$typerelatset = 55;
			}else if($data['invoice']['irs_type'] == 27){
				$typerelatset = 113;
			}else if($data['invoice']['irs_type'] == 28){
				$typerelatset = 114;
			}
			
			if($draf == 0){
				$data['docs_origem'] = $this->related->getRelated($tid,0,0,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,0);
			}else{
				$data['docs_origem'] = $this->related->getRelated($tid,0,1,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,1);
			}
			///////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////
			
			
			$data['employee'] = $this->invocies->employee($data['invoice']['eid']);
			$data['c_custom_fields'] = [];
            if (CUSTOM) {
                $data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
                //$data['i_custom_fields'] = $this->custom->view_fields_data($tid, 2, 1);
            }
			
			$data['invoice']['type'] = $this->lang->line('Invoice');
			$this->load->model('billing_model', 'billing');
			$online_pay = $this->billing->online_pay_settings();
			if ($online_pay['enable'] == 1) {
				//$token = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
				//$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
				//$static_q = $data['qrc'];
				//$qrCode = new QrCode(base_url('billing/card?id=' . $tid . '&itype=inv&token=' . $token));
				//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			}
			
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			
			
			$codQRD = 'A:'.$data['invoice']['loc_country'].$data['invoice']['loc_taxid'].'*';
			$codQRD .= 'B:'.$data['invoice']['taxid'].'*';
			$codQRD .= 'C:'.$data['invoice']['country'].'*';
			$codQRD .= 'D:'.$data['invoice']['irs_type_s'].'*';
			
			//“N” – Normal; “S” – Autofaturação; “A” – Documento anulado; “R” – Documento de resumo doutros documentos criados noutras aplicações e gerado nesta aplicação; “F” – Documento faturado
			
			if($data['invoice']['status'] == 'canceled')
			{
				$codQRD .= 'E:A*';
			}else{
				$codQRD .= 'E:N*';
			}
			
			$date = new DateTime($data['invoice']['invoicedate']);
			//$date = $date->format('Y-m-dTH:i:s');
			$date = $date->format('Ymd');
			
			$codQRD .= 'F:'.$date.'*';
			$codQRD .= 'G:'.$data['invoice']['irs_type_s'].$data['invoice']['serie_name'].'/'.$data['invoice']['tid'].'*';
			
			
			if($draf == 0){
				if(isset($data['invoice']['atc_serie']))
				{
					if($data['invoice']['atc_serie'] == "")
					{
						$codQRD .= 'H:0*';
					}else{
						$codQRD .= 'H:'.$data['invoice']['atc_serie'].$data['invoice']['tid'].'*';
					}
				}else{
					$codQRD .= 'H:0*';
				}
			}
			
			//////////////////////Se tiver Produtos temos de fazer isto/////////////////////////////////////
			$taxasprodutosiva = [];			
			if (is_array($data['products']) && !empty($data['products'])) {
				foreach ($data['products'] as $row) {
					$myArraytaxname = explode(";", $row['taxaname']);
					$myArraytaxcod = explode(";", $row['taxacod']);
					$myArraytaxvals = explode(";", $row['taxavals']);
					$myArraytaxperc = explode(";", $row['taxaperc']);
					$myArraytaxComo = explode(";", $row['taxacomo']);
					for($i = 0; $i < count($myArraytaxname); $i++)
					{
						$jatem = false;
						for($oo = 0; $oo < count($taxasprodutosiva); $oo++)
						{
							if($taxasprodutosiva[$oo]['title'] == $myArraytaxname[$i])
							{
								$taxasprodutosiva[$oo]['val'] = ($taxasprodutosiva[$oo]['val']+$myArraytaxvals[$i]);
								$taxasprodutosiva[$oo]['inci'] = ($taxasprodutosiva[$oo]['inci']+$row['subtotal']);
								$jatem = true;
																
								if($taxasprodutosiva[$oo]['typ'] == '2'){
									$nameise = $this->common->withholdingsidname($myArraytaxComo[$i]);
									if($taxasprodutosiva[$oo]['nameise'] != $nameise){
										$taxasprodutosiva[$oo]['val'] = $nameise;
										$jatem = false;
									}else{
										$taxasprodutosiva[$oo]['val'] = $taxasprodutosiva[$oo]['nameise'];
										break;
									}
								}else{
									 break;
								}
							}
						}
						
						if(!$jatem)
						{
							$nameiselog = '';
							$typ = '0';
							$valcomo = $myArraytaxComo[$i];
							if($valcomo > 1){
								$typ = '2';
								$this->load->library("Common");
								$this->load->library("Custom");
								$nameiselog = $this->common->withholdingsidname($myArraytaxComo[$i]);
							}else if($valcomo == 1){
								$typ = '1';
							}
							$stack = array('como'=> $valcomo, 'title'=>$myArraytaxname[$i], 'val'=>$myArraytaxvals[$i], 'total'=> $myArraytaxvals[$i], 'base'=> $myArraytaxvals[$i], 'cod'=> $myArraytaxcod[$i], 'perc'=>$myArraytaxperc[$i].' %', 'inci'=>$row['subtotal'], 'typ' => $typ, 'nameise' => $nameiselog);
							array_push($taxasprodutosiva, $stack);
						}
					}	
				}	
				
				//“PT-AC” – Espaço fiscal da Região Autónoma dos Açores; e “PT-MA” – Espaço fiscal da Região Autónoma da Madeira
				if($data['invoice']['loc_zon_fis'] == 0)
				{
					$codQRD .= 'J1:PT-AC*';
					for($r = 0; $r < count($taxasprodutosiva); $r++)
					{
						if($taxasprodutosiva[$r]['cod'] == 'ISE')
						{
							$codQRD .= 'J2:'.$taxasprodutosiva[$r]['base'].'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'RED')
						{
							$codQRD .= 'J3:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'J4:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'INT')
						{
							$codQRD .= 'J5:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'J6:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else{
							$codQRD .= 'J7:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'J8:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}
					}
				}else if($data['invoice']['loc_zon_fis'] == 1)
				{
					$codQRD .= 'K1:PT-MA*';
					for($r = 0; $r < count($taxasprodutosiva); $r++)
					{
						if($taxasprodutosiva[$r]['cod'] == 'ISE')
						{
							$codQRD .= 'K2:'.$taxasprodutosiva[$r]['base'].'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'RED')
						{
							$codQRD .= 'K3:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'K4:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'INT')
						{
							$codQRD .= 'K5:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'K6:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else{
							$codQRD .= 'K7:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'K8:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}
					}
				}else{
					$codQRD .= 'I1:'.$data['invoice']['loc_country'].'*';
					for($r = 0; $r < count($taxasprodutosiva); $r++)
					{
						if($taxasprodutosiva[$r]['cod'] == 'ISE')
						{
							$codQRD .= 'I2:'.$taxasprodutosiva[$r]['base'].'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'RED')
						{
							$codQRD .= 'I3:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'I4:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else if($taxasprodutosiva[$r]['cod'] == 'INT')
						{
							$codQRD .= 'I5:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'I6:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}else{
							$codQRD .= 'I7:'.@number_format($taxasprodutosiva[$r]['base'], 2, '.', '').'*';
							$codQRD .= 'I8:'.@number_format($taxasprodutosiva[$r]['total'], 2, '.', '').'*';
						}
					}
				}
			}
			
			$data['taxasprodutosiva'] = $taxasprodutosiva;
			
			//$codQRD .= 'L:'.'*';
			//$codQRD .= 'M:'.'*';
			
			$codQRD .= 'N:'.@number_format($data['invoice']['tax'], 2, '.', '').'*';
			$codQRD .= 'O:'.@number_format($data['invoice']['total'], 2, '.', '').'*';
			
			//Valor do total das retenções na fonte - campo WithholdingTaxAmount do SAF-T (PT).
			//$codQRD .= 'P:'.'*';
			
			
			//4 carateres do Hash gerados na criação do documento e buscar a AT
			$codQRD .= 'Q:'.'*';
			if($draf == 0){
				$codQRD .= 'R:'.$data['invoice']['loc_certification'].'*';
				$campfim = "";
				if($data['invoice']['loc_contabancaria'] != null)
				{
					$campfim .= "IBAN-".$data['invoice']['loc_contabancaria'];
				}
				
				$campfim .= ";".$data['invoice']['loc_cname'];
				$codQRD .= 'S:'.$campfim.'*';
			}
			
			$qrCode = new QrCode($codQRD);
			//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$writer = new PngWriter();
			$writer->write($qrCode)->saveToFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			ini_set('memory_limit', '64M');
		
            $data['round_off'] = $this->custom->api_config(4);
            $pref = $data['invoice']['irs_type_n'];
            $data['general'] = array('title' => $data['invoice']['irs_type_s'].' - '.$data['invoice']['irs_type_n'], 'person' => $this->lang->line('Customer'), 'prefix' => $pref, 't_type' => 0);
			
			$data['Tipodoc'] = "Original";
			$data2 = $data;
			$data2['Tipodoc'] = "Duplicado";
			$data3 = $data;
			$data3['Tipodoc'] = "Triplicado";
			$data4 = $data;
			$data4['Tipodoc'] = "Quadruplicado";
			
			$tempVersion = 1;
			if($temp == null)
			{
				$tempVersion = INVV;
			}else{
				$tempVersion = $temp;
			}

            //But the first serialized its there the data....
            $html = $this->load->view('print_files/invoice-a4_v' . $tempVersion, $data, true);
			$html2 = $this->load->view('print_files/invoice-a4_v' . $tempVersion, $data2, true);
			$html3 = $this->load->view('print_files/invoice-a4_v' . $tempVersion, $data3, true);
			$html4 = $this->load->view('print_files/invoice-a4_v' . $tempVersion, $data4, true);
			
            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_split(array('margin_top' => 10));
			$loc2 = location(0);
			$footer = '<hr><div style="text-align: right;font-family: serif; font-size: 6pt; color: #5C5C5C; font-style: italic;margin-top:-20pt;"><br>'.$data['invoice']['terms'].'<br>';
			if ($data['invoice']['notes'])
			{
				$footer .= $this->lang->line('Note').': '.$data['invoice']['notes'].'<br>';
			}
			
			$footer .= 'Processado por Programa Certificado nº '.$loc2['certification'].' {PAGENO}/{nbpg} </div>';
			$pdf->SetHTMLFooter($footer);
			if($draf == 0){
				if($data['invoice']['numcop'] == 'copy1')
				{
					$pdf->WriteHTML($html);
				}else if($data['invoice']['numcop'] == 'copy2')
				{
					$pdf->WriteHTML($html);
					$pdf->AddPage();
					$pdf->WriteHTML($html2);
				}else if($data['invoice']['numcop'] == 'copy3')
				{
					$pdf->WriteHTML($html);
					$pdf->AddPage();
					$pdf->WriteHTML($html2);
					$pdf->AddPage();
					$pdf->WriteHTML($html3);
				}else if($data['invoice']['numcop'] == 'copy4')
				{
					$pdf->WriteHTML($html);
					$pdf->AddPage();
					$pdf->WriteHTML($html2);
					$pdf->AddPage();
					$pdf->WriteHTML($html3);
					$pdf->AddPage();
					$pdf->WriteHTML($html4);
				}
			}else{
				$pdf->WriteHTML($html);
			}
			
			$file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $data['invoice']['irs_type_s'] . '_' . $data['invoice']['tid']);
            if ($this->input->get('d')) {
                $pdf->Output($file_name . '.pdf', 'D');
            } else {
                $pdf->Output($file_name . '.pdf', 'I');
            }
        }
    }


    public function printquote()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', 'q' . $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {
            $this->load->model('quote_model', 'quote');
            $data['id'] = $tid;
            $data['title'] = $this->lang->line('Quote')." $tid";
            $data['invoice'] = $this->quote->quote_details($tid);
            $data['employee'] = $this->quote->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
            $data['general'] = array('title' => $this->lang->line('Quote'), 'person' => $this->lang->line('Customer'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 1);
			$data['invoice']['type'] = $this->lang->line('Quote');
			
			if($data['invoice']['status'] == 'draft'){
				$data['invoice']['status'] = 'Rascunho';
			}else if($data['invoice']['status'] == 'pending'){
				$data['invoice']['status'] = 'Orc1';
			}else if($data['invoice']['status'] == 'accepted'){
				$data['invoice']['status'] = 'Orc2';
			}else if($data['invoice']['status'] == 'customer_approved'){
				$data['invoice']['status'] = 'Orc3';
			}else if($data['invoice']['status'] == 'rejected'){
				$data['invoice']['status'] = 'Orc4';
			}
			
			///////////////////////////////////////////////////////////////////////
			////////////////////////Relação entre documentos//////////////////////
			$data['relationid'] = $data['invoice']['factura_duplicada'];
			$data['tiprelated'] = 0;
			$this->load->library("Related");
			$typerelatset = 0;
			if($data['invoice']['irs_type'] == 8){
				$typerelatset = 3;
			}else if($data['invoice']['irs_type'] == 10){
				$typerelatset = 12;
			}
			
			if($draf == 0){
				$data['docs_origem'] = $this->related->getRelated($tid,0,0,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,0);
			}else{
				$data['docs_origem'] = $this->related->getRelated($tid,0,1,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,1);
			}
			///////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////
			$token = $this->input->get('token');
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			
			
			$codQRD = 'A:'.$data['invoice']['loc_country'].$data['invoice']['loc_taxid'].'*';
			$codQRD .= 'B:'.$data['invoice']['taxid'].'*';
			$codQRD .= 'C:'.$data['invoice']['country'].'*';
			$codQRD .= 'D:'.$data['invoice']['irs_type_s'].'*';
			
			//“N” – Normal; “S” – Autofaturação; “A” – Documento anulado; “R” – Documento de resumo doutros documentos criados noutras aplicações e gerado nesta aplicação; “F” – Documento faturado
			
			if($data['invoice']['status'] == 'canceled')
			{
				$codQRD .= 'E:A*';
			}else{
				$codQRD .= 'E:N*';
			}
			
			$date = new DateTime($data['invoice']['invoicedate']);
			//$date = $date->format('Y-m-dTH:i:s');
			$date = $date->format('Ymd');
			
			$codQRD .= 'F:'.$date.'*';
			$codQRD .= 'G:'.$data['invoice']['irs_type_s'].$data['invoice']['serie_name'].'/'.$data['invoice']['tid'].'*';
			if($data['invoice']['atc_serie'] == "")
			{
				$codQRD .= 'H:0*';
			}else{
				$codQRD .= 'H:'.$data['invoice']['atc_serie'].$data['invoice']['tid'].'*';
			}
			
			$arrtudo = [];
			foreach ($data['products'] as $row) {
				$myArraytaxname = explode(";", $row['taxaname']);
				$myArraytaxcod = explode(";", $row['taxacod']);
				$myArraytaxvals = explode(";", $row['taxavals']);
				$myArraytaxcomo = explode(";", $row['taxacomo']);
				$myArraytaxperc = explode(";", $row['taxaperc']);
				for($i = 0; $i < count($myArraytaxname); $i++)
				{
					$jatem = false;
					for($oo = 0; $oo < count($arrtudo); $oo++)
					{
						if($arrtudo[$oo]['title'] == $myArraytaxname[$i])
						{
							$arrtudo[$oo]['total'] = ($arrtudo[$oo]['total']+$myArraytaxvals[$i]);
							$jatem = true;
							break;
						}
					}
					
					if(!$jatem)
					{
						$stack = array('title'=>$myArraytaxname[$i], 'total'=>$myArraytaxvals[$i], 'base'=>$myArraytaxvals[$i], 'cod'=>$myArraytaxcod[$i], 'como'=>$myArraytaxcomo[$i], 'perc'=>$myArraytaxperc[$i]);
						array_push($arrtudo, $stack);
					}
				}
			}
			
			//“PT-AC” – Espaço fiscal da Região Autónoma dos Açores; e “PT-MA” – Espaço fiscal da Região Autónoma da Madeira
			if($data['invoice']['loc_zon_fis'] == 0)
			{
				$codQRD .= 'J1:PT-AC*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'J2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'J3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'J5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'J7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else if($data['invoice']['loc_zon_fis'] == 1)
			{
				$codQRD .= 'K1:PT-MA*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'K2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'K3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'K5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'K7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else{
				$codQRD .= 'I1:'.$data['invoice']['loc_country'].'*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'I2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'I3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'I5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'I7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}

			
			
			
			//$codQRD .= 'L:'.'*';
			//$codQRD .= 'M:'.'*';
			
			$codQRD .= 'N:'.@number_format($data['invoice']['tax'], 2, '.', '').'*';
			$codQRD .= 'O:'.@number_format($data['invoice']['total'], 2, '.', '').'*';
			
			//Valor do total das retenções na fonte - campo WithholdingTaxAmount do SAF-T (PT).
			//$codQRD .= 'P:'.'*';
			
			
			//4 carateres do Hash gerados na criação do documento e buscar a AT
			$codQRD .= 'Q:'.'*';
			
			
			$codQRD .= 'R:'.$data['invoice']['loc_certification'].'*';
			
			$campfim = "";
			if($data['invoice']['loc_contabancaria'] != null)
			{
				$campfim .= "IBAN-".$data['invoice']['loc_contabancaria'];
			}
			
			$campfim .= ";".$data['invoice']['loc_cname'];
			$codQRD .= 'S:'.$campfim.'*';
			
			$qrCode = new QrCode($codQRD);
			//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$writer = new PngWriter();
			$writer->write($qrCode)->saveToFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			ini_set('memory_limit', '64M');
			
			$data['Tipodoc'] = "Original";
			$data2 = $data;
			$data2['Tipodoc'] = "Duplicado";
			
			$html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
			$html2 = $this->load->view('print_files/invoice-a4_v' . INVV, $data2, true);
			//PDF Rendering
			$this->load->library('pdf');
			$pdf = $this->pdf->load_split(array('margin_top' => 5));
			$loc2 = location(0);
			$pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">Processado por Programa Certificado nº'.$loc2['certification'].' {PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');

			$pdf->WriteHTML($html);
			$file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Orcamento_' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
            if ($this->input->get('d')) {
                $pdf->Output($file_name . '.pdf', 'D');
            } else {
                $pdf->Output($file_name . '.pdf', 'I');
            }
        }
    }


    public function printorder()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
		$draf = intval($this->input->get('draf'));
		$ext = intval($this->input->get('ext'));
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', 'p' . $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {
            $this->load->model('purchase_model', 'purchase');

            $data['id'] = $tid;
            $data['title'] = $this->lang->line('Purchase Order')." $tid";
            $data['invoice'] = $this->purchase->purchase_details($tid);
            $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
			
			///////////////////////////////////////////////////////////////////////
			////////////////////////Relação entre documentos//////////////////////
			$data['relationid'] = $data['invoice']['factura_duplicada'];
			$data['tiprelated'] = 0;
			$this->load->library("Related");
			$typerelatset = 0;
			if($data['invoice']['irs_type'] == 5){
				$typerelatset = 6;
			}
			
			if($draf == 0){
				$data['docs_origem'] = $this->related->getRelated($tid,0,0,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,0);
			}else{
				$data['docs_origem'] = $this->related->getRelated($tid,0,1,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,1);
			}
			///////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////
			
            $data['general'] = array('title' => $this->lang->line('Purchase Order'), 'person' => $this->lang->line('Supplier'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
            ini_set('memory_limit', '64M');
			
			$data['Tipodoc'] = "Original";
			$token = $this->input->get('token');
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			
			
			$codQRD = 'A:'.$data['invoice']['loc_country'].$data['invoice']['loc_taxid'].'*';
			$codQRD .= 'B:'.$data['invoice']['taxid'].'*';
			$codQRD .= 'C:'.$data['invoice']['country'].'*';
			$codQRD .= 'D:'.$data['invoice']['irs_type_s'].'*';
			
			//“N” – Normal; “S” – Autofaturação; “A” – Documento anulado; “R” – Documento de resumo doutros documentos criados noutras aplicações e gerado nesta aplicação; “F” – Documento faturado
			
			if($data['invoice']['status'] == 'canceled')
			{
				$codQRD .= 'E:A*';
			}else{
				$codQRD .= 'E:N*';
			}
			
			$date = new DateTime($data['invoice']['invoicedate']);
			//$date = $date->format('Y-m-dTH:i:s');
			$date = $date->format('Ymd');
			
			$codQRD .= 'F:'.$date.'*';
			$codQRD .= 'G:'.$data['invoice']['irs_type_s'].$data['invoice']['serie_name'].'/'.$data['invoice']['tid'].'*';
			if($data['invoice']['atc_serie'] == "")
			{
				$codQRD .= 'H:0*';
			}else{
				$codQRD .= 'H:'.$data['invoice']['atc_serie'].$data['invoice']['tid'].'*';
			}
			
			$arrtudo = [];
			foreach ($data['products'] as $row) {
				$myArraytaxname = explode(";", $row['taxaname']);
				$myArraytaxcod = explode(";", $row['taxacod']);
				$myArraytaxvals = explode(";", $row['taxavals']);
				$myArraytaxcomo = explode(";", $row['taxacomo']);
				$myArraytaxperc = explode(";", $row['taxaperc']);
				for($i = 0; $i < count($myArraytaxname); $i++)
				{
					$jatem = false;
					for($oo = 0; $oo < count($arrtudo); $oo++)
					{
						if($arrtudo[$oo]['title'] == $myArraytaxname[$i])
						{
							$arrtudo[$oo]['total'] = ($arrtudo[$oo]['total']+$myArraytaxvals[$i]);
							$jatem = true;
							break;
						}
					}
					
					if(!$jatem)
					{
						$stack = array('title'=>$myArraytaxname[$i], 'total'=>$myArraytaxvals[$i], 'base'=>$myArraytaxvals[$i], 'cod'=>$myArraytaxcod[$i], 'como'=>$myArraytaxcomo[$i], 'perc'=>$myArraytaxperc[$i]);
						array_push($arrtudo, $stack);
					}
				}
			}
			
			//“PT-AC” – Espaço fiscal da Região Autónoma dos Açores; e “PT-MA” – Espaço fiscal da Região Autónoma da Madeira
			if($data['invoice']['loc_zon_fis'] == 0)
			{
				$codQRD .= 'J1:PT-AC*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'J2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'J3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'J5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'J7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else if($data['invoice']['loc_zon_fis'] == 1)
			{
				$codQRD .= 'K1:PT-MA*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'K2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'K3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'K5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'K7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else{
				$codQRD .= 'I1:'.$data['invoice']['loc_country'].'*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'I2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'I3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'I5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'I7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}

			
			
			
			//$codQRD .= 'L:'.'*';
			//$codQRD .= 'M:'.'*';
			
			$codQRD .= 'N:'.@number_format($data['invoice']['tax'], 2, '.', '').'*';
			$codQRD .= 'O:'.@number_format($data['invoice']['total'], 2, '.', '').'*';
			
			//Valor do total das retenções na fonte - campo WithholdingTaxAmount do SAF-T (PT).
			//$codQRD .= 'P:'.'*';
			
			
			//4 carateres do Hash gerados na criação do documento e buscar a AT
			$codQRD .= 'Q:'.'*';
			
			
			$codQRD .= 'R:'.$data['invoice']['loc_certification'].'*';
			
			$campfim = "";
			if($data['invoice']['loc_contabancaria'] != null)
			{
				$campfim .= "IBAN-".$data['invoice']['loc_contabancaria'];
			}
			
			$campfim .= ";".$data['invoice']['loc_cname'];
			$codQRD .= 'S:'.$campfim.'*';
			
			$qrCode = new QrCode($codQRD);
			//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$writer = new PngWriter();
			$writer->write($qrCode)->saveToFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			ini_set('memory_limit', '64M');
			$data['Tipodoc'] = "Original";
			$data2 = $data;
			$data2['Tipodoc'] = "Duplicado";
			
			$html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
			$html2 = $this->load->view('print_files/invoice-a4_v' . INVV, $data2, true);
			//PDF Rendering
			$this->load->library('pdf');
			$pdf = $this->pdf->load_split(array('margin_top' => 10));
            $loc2 = location(0);
			$pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">Processado por Programa Certificado nº'.$loc2['certification'].' {PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');

            $pdf->WriteHTML($html);
            if ($this->input->get('d')) {

                $pdf->Output('Purchase_#' . $tid . '.pdf', 'D');
            } else {
                $pdf->Output('Purchase_#' . $tid . '.pdf', 'I');
            }


        }

    }

    public function printstockreturn()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $data['id'] = $tid;
            $data['title'] = $this->lang->line('Stock Return')." $tid";
            $data['invoice'] = $this->stockreturn->purchase_details($tid);
            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
			
			///////////////////////////////////////////////////////////////////////
			////////////////////////Relação entre documentos//////////////////////
			$data['relationid'] = $data['invoice']['factura_duplicada'];
			$data['tiprelated'] = 0;
			$this->load->library("Related");
			$typerelatset = 0;
			if($data['invoice']['irs_type'] == 6){
				$typerelatset = 17;
			}
			
			if($draf == 0){
				$data['docs_origem'] = $this->related->getRelated($tid,0,0,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,0,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,0);
			}else{
				$data['docs_origem'] = $this->related->getRelated($tid,0,1,$typerelatset,0);
				$data['docs_deu_origem'] = $this->related->getRelated(0,$tid,1,0,$typerelatset);
				$data['products'] = $this->related->detailsAfterRelationProducts($tid,$typerelatset,1);
			}
			///////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////
			
			
            $data['round_off'] = $this->custom->api_config(4);
            $ty = $this->input->get('ty');
			
            if ($ty < 2) {
                if ($data['invoice']['i_class'] == 1) {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Customer'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
					$data['invoice']['type'] = $this->lang->line('Stock Return');
                } else {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
					$data['invoice']['type'] = $this->lang->line('Stock Return');
                }
            } else {
                $data['general'] = array('title' => $this->lang->line('Credit Note'), 'person' => $this->lang->line('Customer'), 'prefix' => $data['invoice']['irs_type_n'], 't_type' => 0);
				$data['invoice']['type'] = $this->lang->line('Credit Note');
            }

			$data['invoice']['irs_type'] = "STKR ";
			$token = $this->input->get('token');
			$data['qrc'] = 'pos_' . date('Y_m_d_H_i_s') . '_.png';
			$static_q = $data['qrc'];
			
			
			$codQRD = 'A:'.$data['invoice']['loc_country'].$data['invoice']['loc_taxid'].'*';
			$codQRD .= 'B:'.$data['invoice']['taxid'].'*';
			$codQRD .= 'C:'.$data['invoice']['country'].'*';
			$codQRD .= 'D:'.$data['invoice']['irs_type_s'].'*';
			
			//“N” – Normal; “S” – Autofaturação; “A” – Documento anulado; “R” – Documento de resumo doutros documentos criados noutras aplicações e gerado nesta aplicação; “F” – Documento faturado
			
			if($data['invoice']['status'] == 'canceled')
			{
				$codQRD .= 'E:A*';
			}else{
				$codQRD .= 'E:N*';
			}
			
			$date = new DateTime($data['invoice']['invoicedate']);
			//$date = $date->format('Y-m-dTH:i:s');
			$date = $date->format('Ymd');
			
			$codQRD .= 'F:'.$date.'*';
			$codQRD .= 'G:'.$data['invoice']['irs_type_s'].$data['invoice']['serie_name'].'/'.$data['invoice']['tid'].'*';
			if($data['invoice']['atc_serie'] == "")
			{
				$codQRD .= 'H:0*';
			}else{
				$codQRD .= 'H:'.$data['invoice']['atc_serie'].$data['invoice']['tid'].'*';
			}
			
			$arrtudo = [];
			foreach ($data['products'] as $row) {
				$myArraytaxname = explode(";", $row['taxaname']);
				$myArraytaxcod = explode(";", $row['taxacod']);
				$myArraytaxvals = explode(";", $row['taxavals']);
				$myArraytaxcomo = explode(";", $row['taxacomo']);
				$myArraytaxperc = explode(";", $row['taxaperc']);
				for($i = 0; $i < count($myArraytaxname); $i++)
				{
					$jatem = false;
					for($oo = 0; $oo < count($arrtudo); $oo++)
					{
						if($arrtudo[$oo]['title'] == $myArraytaxname[$i])
						{
							$arrtudo[$oo]['total'] = ($arrtudo[$oo]['total']+$myArraytaxvals[$i]);
							$jatem = true;
							break;
						}
					}
					
					if(!$jatem)
					{
						$stack = array('title'=>$myArraytaxname[$i], 'total'=>$myArraytaxvals[$i], 'base'=>$myArraytaxvals[$i], 'cod'=>$myArraytaxcod[$i], 'como'=>$myArraytaxcomo[$i], 'perc'=>$myArraytaxperc[$i]);
						array_push($arrtudo, $stack);
					}
				}
			}
			
			//“PT-AC” – Espaço fiscal da Região Autónoma dos Açores; e “PT-MA” – Espaço fiscal da Região Autónoma da Madeira
			if($data['invoice']['loc_zon_fis'] == 0)
			{
				$codQRD .= 'J1:PT-AC*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'J2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'J3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'J5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'J7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'J8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else if($data['invoice']['loc_zon_fis'] == 1)
			{
				$codQRD .= 'K1:PT-MA*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'K2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'K3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'K5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'K7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'K8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}else{
				$codQRD .= 'I1:'.$data['invoice']['loc_country'].'*';
				for($r = 0; $r < count($arrtudo); $r++)
				{
					if($arrtudo[$r]['cod'] == 'ISE')
					{
						$codQRD .= 'I2:'.$arrtudo[$r]['base'].'*';
					}else if($arrtudo[$r]['cod'] == 'RED')
					{
						$codQRD .= 'I3:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I4:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else if($arrtudo[$r]['cod'] == 'INT')
					{
						$codQRD .= 'I5:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I6:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}else{
						$codQRD .= 'I7:'.@number_format($arrtudo[$r]['base'], 2, '.', '').'*';
						$codQRD .= 'I8:'.@number_format($arrtudo[$r]['total'], 2, '.', '').'*';
					}
				}
			}

			
			
			
			//$codQRD .= 'L:'.'*';
			//$codQRD .= 'M:'.'*';
			
			$codQRD .= 'N:'.@number_format($data['invoice']['tax'], 2, '.', '').'*';
			$codQRD .= 'O:'.@number_format($data['invoice']['total'], 2, '.', '').'*';
			
			//Valor do total das retenções na fonte - campo WithholdingTaxAmount do SAF-T (PT).
			//$codQRD .= 'P:'.'*';
			
			
			//4 carateres do Hash gerados na criação do documento e buscar a AT
			$codQRD .= 'Q:'.'*';
			
			
			$codQRD .= 'R:'.$data['invoice']['loc_certification'].'*';
			
			$campfim = "";
			if($data['invoice']['loc_contabancaria'] != null)
			{
				$campfim .= "IBAN-".$data['invoice']['loc_contabancaria'];
			}
			
			$campfim .= ";".$data['invoice']['loc_cname'];
			$codQRD .= 'S:'.$campfim.'*';
			
			$qrCode = new QrCode($codQRD);
			//$qrCode->writeFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			
			$writer = new PngWriter();
			$writer->write($qrCode)->saveToFile(FCPATH . 'userfiles/pos_temp/' . $data['qrc']);
			ini_set('memory_limit', '64M');
			
			$data['Tipodoc'] = "Original";
			
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
            $loc2 = location(0);
			$pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">Processado por Programa Certificado nº'.$loc2['certification'].' {PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');
            $pdf->WriteHTML($html);
            if ($this->input->get('d')) {
                $pdf->Output('Stockreturn_order#' . $tid . '.pdf', 'D');
            } else {
                $pdf->Output('Stockreturn_order#' . $tid . '.pdf', 'I');
            }
        }
    }


    public function card()
    {
        if (!$this->input->get()) {
            exit();
        }
        $data['redirect_u'] = '';
        if (isset($_COOKIE['pos_set'])) {
            $data['redirect_u'] = $_COOKIE['pos_set'];
            setcookie("pos_set", null, -1, '/');
        }
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['enable'] == 0) {
            exit();
        }
        $data['tid'] = $this->input->get('id');
        $data['token'] = $this->input->get('token');
        $data['itype'] = $this->input->get('itype');
        $data['gid'] = $this->input->get('gid');
        if ($data['itype'] == 'inv') {
            $validtoken = hash_hmac('ripemd160', $data['tid'], $this->config->item('encryption_key'));
            if (hash_equals($data['token'], $validtoken)) {
                $data['invoice'] = $this->invocies->invoice_details($data['tid'], '', false);
                $data['company'] = location($data['invoice']['loc']);
            } else {
                exit();
            }
        }
        switch ($data['gid']) {
            case 1:
                $fname = 'stripe';
                break;
            case 2:
                $fname = 'authorize';
                break;
            case 3:
                $fname = 'pinpay';
                break;
            case 4:
                $fname = 'paypal';
                break;
            case 5:
                $fname = 'securepay';
                break;
            case 6:
                $fname = 'checkout';
                break;
            case 7:
                $fname = 'payumoney';
                break;
            case 8:
                $fname = 'razor';
                break;
            default :
                $fname = 'stripe';
                break;
        }
        $online_pay = $this->billing->online_pay_settings();
        $data['gateway'] = $this->billing->gateway($data['gid']);
        if ($online_pay['enable'] == 1) {
            $this->load->view('billing/header');
            $this->load->view('gateways/card_' . $fname, $data);
            $this->load->view('billing/footer');
        } else {
            echo '<h3>' . $this->lang->line('Online Payment Service') . '</h3>';
        }
    }

    public function process_card()
    {
        if (!$this->input->post()) {
            exit();
        }
        $tid = $this->input->post('id', true);
        $itype = $this->input->post('itype', true);
        $gateway = $this->input->post('gateway', true);

        $amount = number_format($this->input->post('amount', true), 2, '.', '');

        if ($itype == 'inv') {
            $customer = $this->invocies->invoice_details($tid, null, false);
            if (!$customer['tid']) {
                exit();
            }
        }
        $hash = $this->input->post('token', true);

        $cardNumber = $this->input->post('cardNumber', true);
        $cardExpiry = $this->input->post('cardExpiry', true);
        $cardCVC = $this->input->post('cardCVC', true);
        $nmonth = substr($cardExpiry, 0, 2);
        $nyear = '20' . substr($cardExpiry, 5, 2);
        $note = 'Card Payment for #' . $customer['tid'];
        $pmethod = 'Card';
        $amount_o = $amount;
        if ($customer['multi'] > 0) {
            $multi_currency = $this->invocies->currency_d($customer['multi']);
            //    $amount =  $amount;
            $gateway_data['currency'] = $multi_currency['code'];
            $note .= ' (Currency Conversion Applied)';
        }
        if ($customer['loc'] > 0) {
            $multi_currency = $this->invocies->currency_d($customer['multi'], $customer['loc']);
            //        $amount =  $amount;
            $gateway_data['currency'] = $multi_currency['code'];
            $note .= ' (Currency Conversion Applied)';
        }
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        $gateway_data = $this->billing->gateway($gateway);
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $amount = number_format($amount_t, 2, '.', '');
        if (hash_equals($hash, $validtoken)) {
            switch ($gateway) {
                case 1:

                    $response = $this->stripe($this->input->post('paymentMethodId', true), number_format($amount, 0, '', ''), $gateway_data, $tid, $customer, '', $this->input->post('paymentIntentId', true));
                    break;
                case 2:
                    $response = $this->authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 3:
                    $response = $this->pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 4:
                    $response = $this->paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 5:
                    $response = $this->securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data);
                    break;
                case 6:
                    $response = $this->twocheckout($this->input->post('auth_token', true), $amount, $tid, $gateway_data, $customer);
                    break;
            }
            // Process response

            if ($gateway > 1) {
                if ($response->isSuccessful()) {

                    $amount_o = rev_amountExchange_s($amount_o, $customer['multi'], $customer['loc']);
                    if ($this->billing->paynow($tid, $amount_o, $note, $pmethod, $customer['loc'])) {
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'Success', 'message' =>
                            $this->lang->line('Thank you for the payment') . " <a href='" . base_url('billing/view?id=' . $tid . '&token=' . $hash) . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
                    }
                } elseif ($response->isRedirect()) {
                    // Redirect to offsite payment gateway
                    $response->redirect();
                } else {
                    // Payment failed
                    echo json_encode(array('status' => 'Error', 'message' =>
                        $this->lang->line('Payment failed')));
                }
            } elseif ($gateway == 1 and @$response['status'] == 'succeeded') {
                $amount_o = rev_amountExchange_s(($amount - $surcharge) / 100, $customer['multi'], $customer['loc']);
                if ($this->billing->paynow($tid, $amount_o, $note, $pmethod, $customer['loc'])) {
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'Success', 'clientSecret' => $response['clientSecret'], 'message' =>
                        $this->lang->line('Thank you for the payment') . " <a href='" . base_url('billing/view?id=' . $tid . '&token=' . $hash) . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));

                }
            } elseif ($gateway == 1 and @$response['status'] == 'error') {
                header('Content-Type: application/json');
                echo json_encode(array('error' => $response['message']));
            }

        }
    }
	
	
	private function stripe($token, $amount, $gateway_data, $tid, $customer, $currency = '', $token_id = '')
    {
        require_once APPPATH . 'third_party/stripe-php/vendor/autoload.php';
        \Stripe\Stripe::setApiKey($gateway_data['key1']);
        try {
            if ($token) {
                // Create new PaymentIntent with a PaymentMethod ID from the client.
                $intent = \Stripe\PaymentIntent::create([
                    "amount" => $amount,
                    "currency" => $gateway_data['currency'],
                    "payment_method" => $token,
                    "confirmation_method" => "manual",
                    "confirm" => true,
                    // If a mobile client passes `useStripeSdk`, set `use_stripe_sdk=true`
                    // to take advantage of new authentication features in mobile SDKs
                    "use_stripe_sdk" => true,

                ]);
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }
                // After create, if the PaymentIntent's status is succeeded, fulfill the order.
            } else if ($token_id) {
                // Confirm the PaymentIntent to finalize payment after handling a required action
                // on the client.

                $intent = \Stripe\PaymentIntent::retrieve($token_id);
                $intent->confirm();
                // After confirm, if the PaymentIntent's status is succeeded, fulfill the order.
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }

            }

            $output = $this->generateResponse($intent);

            echo json_encode($output);
        } catch (Stripe\Exception\CardException $e) {
            return array('status' => 'error', 'paid_amount' => 0, 'message' => $e->getMessage());

        }

    }
	

    /*private function stripe($token, $amount, $gateway_data, $tid, $customer, $currency = '', $token_id = '')
    {
        require_once APPPATH . 'third_party/stripe-php/vendor/autoload.php';
        \Stripe\Stripe::setApiKey($gateway_data['key1']);
        try {
            if ($token) {
                // Create new PaymentIntent with a PaymentMethod ID from the client.
                $intent = \Stripe\PaymentIntent::create([
                    "amount" => $amount,
                    "currency" => $gateway_data['currency'],
                    "payment_method" => $token,
                    "confirmation_method" => "manual",
                    "confirm" => true,
                    // If a mobile client passes `useStripeSdk`, set `use_stripe_sdk=true`
                    // to take advantage of new authentication features in mobile SDKs
                    "use_stripe_sdk" => true,

                ]);
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }
                // After create, if the PaymentIntent's status is succeeded, fulfill the order.
            } else if ($token_id) {
                // Confirm the PaymentIntent to finalize payment after handling a required action
                // on the client.

                $intent = \Stripe\PaymentIntent::retrieve($token_id);
                $intent->confirm();
                // After confirm, if the PaymentIntent's status is succeeded, fulfill the order.
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }

            }

            $output = $this->generateResponse($intent);

            echo json_encode($output);
        } catch (Stripe\Exception\CardException $e) {
            return array('status' => 'error', 'paid_amount' => 0, 'message' => $e->getMessage());

        }

    }*/


    private function authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {
        $gateway = Omnipay::create('AuthorizeNet_AIM');
        $gateway->setApiLoginId($gateway_data['key2']);
        $gateway->setTransactionKey($gateway_data['key1']);
        $gateway->setDeveloperMode($gateway_data['dev_mode']);
        $meta = array(
            'Name' => $customer['name'],
            'email' => $customer['email']
        );
        try {
            return $gateway->purchase(
                array(
                    'card' => array(
                        'number' => $cardNumber,
                        'expiryMonth' => $nmonth,
                        'expiryYear' => $nyear,
                        'cvv' => $cardCVC
                    ),
                    'amount' => $amount,
                    'currency' => $gateway_data['currency'],
                    'description' => 'Paid for ' . $customer['name'] . ' INV#' . $tid,
                    'metadata' => $meta

                )
            )->send();
        } catch (Exception $e) {
            return 0;
        }
    }


    private function pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {
        $gateway = \Omnipay\Omnipay::create('Pin');

        // Initialise the gateway
        $gateway->initialize(array(
            'secretKey' => $gateway_data['key1'],
            'testMode' => $gateway_data['dev_mode'], // Or false when you are ready for live transactions
        ));

        // Create a credit card object
        // This card can be used for testing.
        // See https://pin.net.au/docs/api/test-cards for a list of card
        // numbers that can be used for testing.
        $card = new \Omnipay\Common\CreditCard(array(
            'firstName' => $customer['name'],
            'lastName' => 'Customer',
            'number' => $cardNumber,
            'expiryMonth' => $nmonth,
            'expiryYear' => $nyear,
            'cvv' => $cardCVC,
            'email' => $customer['email'],
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region'],
        ));

        // Do a purchase transaction on the gateway
        $transaction = $gateway->purchase(array(
            'description' => 'Payment for INV#' . $tid,
            'amount' => $amount,
            'currency' => $gateway_data['currency'],
            'clientIp' => $_SERVER['REMOTE_ADDR'],
            'card' => $card,
        ));
        return $transaction->send();

    }


    private function securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data)
    {
        $gateway = \Omnipay\Omnipay::create('SecurePay_SecureXML');
        $gateway->setMerchantId($gateway_data['key1']);
        $gateway->setTransactionPassword($gateway_data['key2']);
        $gateway->setTestMode($gateway_data['dev_mode']);
        // Create a credit card object
        $card = new \Omnipay\Common\CreditCard(
            [
                'number' => $cardNumber,
                'expiryMonth' => $nmonth,
                'expiryYear' => $nyear,
                'cvv' => $cardCVC,
            ]
        );
        // Perform a purchase test
        $transaction = $gateway->purchase(
            [
                'amount' => $amount,
                'currency' => $gateway_data['currency'],
                'transactionId' => 'invoice_' . $tid,
                'card' => $card,
            ]
        );

        return $transaction->send();
    }

    private function twocheckout($auth_token, $amount, $tid, $gateway_data, $customer)
    {
        $gateway = Omnipay::create('TwoCheckoutPlus_Token');
        $gateway->setAccountNumber($gateway_data['extra']);
        $gateway->setTestMode($gateway_data['dev_mode']);
        $gateway->setPrivateKey($gateway_data['key2']);

        $formData = array(
            'firstName' => $customer['name'],
            'email' => $customer['email'],
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region'],
            "phoneNumber" => $customer['phone'],
        );


        $purchase_request_data = array(
            'card' => $formData,
            'token' => $auth_token,
            'transactionId' => $tid,
            'currency' => $gateway_data['currency'],
            'total' => $amount,
            'amount' => $amount,
        );
        return $gateway->purchase($purchase_request_data)->send();


    }


    private function paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {

        $gateway = Omnipay::create('PayPal_Rest');
        // Initialise the gateway
        $gateway->initialize(array(
            'clientId' => $gateway_data['key1'],
            'secret' => $gateway_data['key2'],
            'testMode' => $gateway_data['dev_mode'], // Or false when you are ready for live transactions
        ));

        $card = new \Omnipay\Common\CreditCard(array(
            'firstName' => $customer['name'],
            'lastName' => 'Customer',
            'number' => $cardNumber,
            'expiryMonth' => $nmonth,
            'expiryYear' => $nyear,
            'cvv' => $cardCVC,
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region']
        ));

        try {
            $transaction = $gateway->purchase(array(
                'amount' => $amount,
                'currency' => $gateway_data['currency'],
                'description' => 'Payment for #inv ' . $tid,
                'card' => $card,
            ));
            return $transaction->send();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function bank()
    {
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['bank'] == 1) {
            $data['accounts'] = $this->billing->bank_accounts('Yes','Yes');
            $this->load->view('billing/header');
            $this->load->view('payment/public_bank_view', $data);
            $this->load->view('billing/footer');
        }

    }


    public function recharge()
    {

        if (!$this->input->get()) {
            exit();
        }
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['enable'] == 0) {
            exit();
        }
        $data['id'] = base64_decode($this->input->get('id', true));


        $data['amount'] = $this->input->get('amount', true);
        $data['gid'] = $this->input->get('gid', true);
        $data['token'] = $this->input->get('token', true);

        switch ($data['gid']) {
            case 1:
                $fname = 'stripe';
                break;
            case 2:
                $fname = 'authorize';
                break;
            case 3:
                $fname = 'pinpay';
                break;
            case 4:
                $fname = 'paypal';
                break;
            case 5:
                $fname = 'securepay';
                break;
            case 6:
                $fname = 'checkout';
                break;
            case 7:
                $fname = 'payumoney';
                break;
            case 8:
                $fname = 'razor';
                break;
            default :
                $fname = 'stripe';
                break;
        }
        $online_pay = $this->billing->online_pay_settings();
        $data['gateway'] = $this->billing->gateway($data['gid']);
        if ($online_pay['enable'] == 1) {
            $this->load->view('billing/header');
            $this->load->view('gateways/recharge/card_' . $fname, $data);
            $this->load->view('billing/footer');
        } else {
            echo '<h3>' . $this->lang->line('Online Payment Service') . '</h3>';
        }

    }

    public function process_recharge()
    {
        if (!$this->input->post()) {
            exit();
        }

        $tid = $this->input->post('id', true);
        $amount = number_format($this->input->post('amount', true), 2, '.', '');
        $gateway = $this->input->post('gateway', true);
        $cardNumber = $this->input->post('cardNumber', true);
        $cardExpiry = $this->input->post('cardExpiry', true);
        $cardCVC = $this->input->post('cardCVC', true);

        $nmonth = substr($cardExpiry, 0, 2);
        $nyear = '20' . substr($cardExpiry, 5, 2);


        $pmethod = 'Card';

        $amount_o = $amount;

        $gateway_data = $this->billing->gateway($gateway);
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $this->load->model('customers_model', 'customers');
        $customer = $this->customers->details($tid, false);
        $note = 'Recharge Card Payment for Customer' . $customer['email'];


        $amount = number_format($amount_t, 2, '.', '');


        switch ($gateway) {

            case 1:
                //       $response = $this->stripe($this->input->post('stripeToken', true), $amount, $gateway_data, $tid, $customer);

                $response = $this->stripe($this->input->post('paymentMethodId', true), number_format($amount, 0, '', ''), $gateway_data, $tid, $customer, '', $this->input->post('paymentIntentId', true));

                break;
            case 2:
                $response = $this->authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 3:
                $response = $this->pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 4:
                $response = $this->paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 5:
                $response = $this->securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data);
                break;
            case 6:
                $response = $this->twocheckout($this->input->post('auth_token', true), $amount, $tid, $gateway_data, $customer);
                break;

        }

        // Process response
        if ($gateway > 1) {
            if ($response->isSuccessful()) {

                if ($this->billing->recharge_done($tid, $amount_o)) {
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'Success', 'message' =>
                        $this->lang->line('Thank you for the payment') . " <a href='" . base_url('crm/payments/recharge') . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
                }

            } elseif ($response->isRedirect()) {

                // Redirect to offsite payment gateway
                $response->redirect();

            } else {

                // Payment failed
                echo json_encode(array('status' => 'Error', 'message' =>
                    $this->lang->line('Payment failed')));
            }
        } elseif ($gateway == 1 and @$response['status'] == 'succeeded') {
            $amount_o = $amount_o / 100;
            if ($this->billing->recharge_done($tid, $amount_o)) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'Success', 'message' =>
                    $this->lang->line('Thank you for the payment') . " <a href='" . base_url('crm/payments/recharge') . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
            }
        } elseif ($gateway == 1 and @$response['status'] == 'error') {
            header('Content-Type: application/json');
            echo json_encode(array('error' => $response['message']));
        }


    }

    public function secureprocess()
    {

        $gid = $this->input->get('g', true);
        //payu
        if ($gid == 7) {
            $status = $this->input->post('status', true);
            $firstname = $this->input->post("firstname", true);
            $amount = $this->input->post("amount", true);
            $txnid = $this->input->post("txnid", true);
            $posted_hash = $this->input->post("hash", true);
            $key = $this->input->post("key", true);
            $productinfo = $this->input->post("productinfo", true);
            $email = $this->input->post("email", true);
            $gateway_data = $this->billing->gateway($gid);
            $salt = $gateway_data['key2'];

// Salt should be same Post Request

            if ($this->input->post('additionalCharges', true)) {
                $additionalCharges = $this->input->post("additionalCharges", true);
                $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
            } else {
                $retHashSeq = $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
            }
            $hash = hash("sha512", $retHashSeq);
            if ($hash != $posted_hash) {
                echo "Invalid Transaction. Please try again";
            } elseif($status=='success') {

                //tt
                $tid = $this->input->get('inv', true);
                $customer = $this->invocies->invoice_details($tid);
                $note = 'Card Payment for #' . $customer['tid'] . ' T#' . $txnid;
                $pmethod = 'Card';
                $amount_o = $customer['total'] - $customer['pamnt'];
                $surcharge = ($amount_o * $gateway_data['surcharge']) / 100;
                $amount_t = $amount_o + $surcharge;
                $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
                if (number_format($amount_t, 2, '.', '') == $amount) {
                    $amount = number_format($amount_o, 2, '.', '');
                    if ($this->billing->paynow($customer['iid'], $amount, $note, $pmethod, $customer['loc'])) {

                        redirect(base_url('billing/view?id=' . $tid . '&token=' . $validtoken));
                    }
                }
            } else {
                   $tid = $this->input->get('inv', true);
                   $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
                echo "Invalid Transaction. Please try again";
                    redirect(base_url('billing/view?id=' . $tid . '&token=' . $validtoken));
            }


        } else {
            $data['gateway_data'] = $this->billing->gateway($gid);
            $data['tid'] = $this->input->get('inv', true);
            $this->load->view('gateways/card_razor_verify', $data);
        }
    }


    public function gateway_process()
    {
        //for paypal
        $invoice = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        $gateway_data = $this->billing->gateway(4);
        $paypalConfig = [
            'sandbox' => $gateway_data['dev_mode'],
            'client_id' => $gateway_data['key1'],
            'client_secret' => $gateway_data['key2'],
            'return_url' => base_url('billing/gateway_response'),
            'cancel_url' => base_url('billing/view?id=' . $invoice . '&token=' . $token)
        ];

        $this->load->library("Paypal_gateway", $paypalConfig);

        $apiContext = $this->paypal_gateway->getApiContext();


        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

// Set some example data for the payment.
        $customer = $this->invocies->invoice_details($invoice);
        if (!$customer['tid']) {
            exit();
        }
        $amount = number_format($this->input->post('amount', true), 2, '.', '');
        if ($customer['multi'] > 0) {
            $multi_currency = $this->invocies->currency_d($customer['multi']);
            //    $amount =  $amount;
            $gateway_data['currency'] = $multi_currency['code'];

        }
        if ($customer['loc'] > 0) {
            $multi_currency = $this->invocies->currency_d($customer['multi'], $customer['loc']);
            //        $amount =  $amount;
            $gateway_data['currency'] = $multi_currency['code'];

        }
        $validtoken = hash_hmac('ripemd160', $invoice, $this->config->item('encryption_key'));
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $amount = number_format($amount_t, 2, '.', '');

        if (hash_equals($token, $validtoken)) {

            $amountPayable = $amount;
            $invoiceNumber = $invoice;

            $amount = new Amount();
            $amount->setCurrency($gateway_data['currency'])
                ->setTotal($amountPayable);

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setDescription('Some description about the payment being made')
                ->setInvoiceNumber($invoiceNumber);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($paypalConfig['return_url'])
                ->setCancelUrl($paypalConfig['cancel_url']);

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

            try {
                $payment->create($apiContext);
                $this->billing->token($invoice, 1);
            } catch (Exception $e) {
                throw new Exception('Unable to create link for payment');
            }

            header('location:' . $payment->getApprovalLink());
            exit(1);
        }


    }

    public function gateway_response()
    {
        if (empty($this->input->get('paymentId', true)) || empty($this->input->get('PayerID', true))) {
            exit;
        }
        $gateway_data = $this->billing->gateway(4);
        $paypalConfig = [
            'sandbox' => $gateway_data['dev_mode'],
            'client_id' => $gateway_data['key1'],
            'client_secret' => $gateway_data['key2'],
            'return_url' => base_url('billing/gateway_response'),
            'cancel_url' => base_url('billing/view?id=105&token=ee2f511d44dd7f0212d46b92f2d6022754574bb3')
        ];
        $this->load->library("Paypal_gateway", $paypalConfig);
        $apiContext = $this->paypal_gateway->getApiContext();
        $paymentId = $_GET['paymentId'];
        $payment = Payment::get($paymentId, $apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        try {
            // Take the payment
            $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
                $data = [
                    'transaction_id' => $payment->getId(),
                    'payment_amount' => $payment->transactions[0]->amount->total,
                    'payment_status' => $payment->getState(),
                    'invoice_id' => $payment->transactions[0]->invoice_number
                ];
                $validtoken = hash_hmac('ripemd160', $data['invoice_id'], $this->config->item('encryption_key'));
                $paypalConfig['bill_url'] = base_url('billing/view?id=' . $data['invoice_id'] . '&token=' . $validtoken);
                if ($data['payment_status'] === 'approved') {
                    $customer = $this->invocies->invoice_details($data['invoice_id']);
                    $amount_o = $data['payment_amount'];

                    $amount_o = rev_amountExchange_s($amount_o, $customer['multi'], $customer['loc']);

                    $note = 'Card Payment for #' . $customer['tid'];
                    $pmethod = 'Card';
                    if ($customer['multi'] > 0) {
                        //    $amount =  $amount;
                        $note .= ' (Currency Conversion Applied)';
                    }
                    if ($customer['loc'] > 0) {

                        $note .= ' (Currency Conversion Applied)';
                    }
                    $amount = $amount_o / (($gateway_data['surcharge'] / 100) + 1);
                    $amount_o = number_format($amount, 2, '.', '');
                    $valid = $this->billing->token($customer['iid'], 2);
                    if ($valid['rid'] == $customer['iid']) {
                        $this->billing->paynow($customer['iid'], $amount_o, $note, $pmethod, $customer['loc']);
                        $this->billing->token($customer['iid'], 3);
                    }
                    header('location:' . $paypalConfig['bill_url']);
                    exit(1);
                } else {
                    // Payment failed
                    header('location:' . $paypalConfig['bill_url']);
                    exit(1);
                }
            } catch (Exception $e) {
                // Failed to retrieve payment from PayPal
                $this->billing->token($customer['iid'], 3);
                header('location:' . base_url());
            }
        } catch (Exception $e) {
            // Failed to take payment
            $this->billing->token($customer['iid'], 3);
            header('location:' . base_url());
        }
    }

    public function process_stripe()
    {
        echo 'Payment processing do no hit back button.....';
    }

    public function stripe_api_response()
    {

        $data['gateway'] = $this->billing->gateway(1);
        echo json_encode(['publishableKey' => $data['gateway']['key2']]);
    }

    function generateResponse($intent)
    {
        switch ($intent->status) {
            case "requires_action":
            case "requires_source_action":
                // Card requires authentication
                return [
                    'requiresAction' => true,
                    'paymentIntentId' => $intent->id,
                    'clientSecret' => $intent->client_secret
                ];
            case "requires_payment_method":
            case "requires_source":
                // Card was not properly authenticated, suggest a new payment method
                return [
                    'error' => "Your card was denied, please provide a new payment method"
                ];
            case "succeeded":
                // Payment is complete, authentication not required
                // To cancel the payment after capture you will need to issue a Refund (https://stripe.com/docs/api/refunds)
                return ['clientSecret' => $intent->client_secret];
        }
    }

}