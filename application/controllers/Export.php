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

class Export extends CI_Controller {

    var $date;

    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        $this->load->model('export_model', 'export');
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
            exit;
        }
        if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->date = 'backup_' . date('Y_m_d_H_i_s');
        $this->li_a = 'export';
    }

    function dbexport()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
		$this->li_a = 'taxstsaft';
        $head['title'] = "Backup Database";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/db_back');
        $this->load->view('fixed/footer');
    }
	
	function dbexport_c()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->dbutil();
        $backup =& $this->dbutil->backup();
        $this->load->helper('file');
        write_file('<?php  echo base_url();?>/downloads', $backup);
        $this->load->helper('download');
        force_download($this->date . '.gz', $backup);
    }

    // this is the function for the download xml


    function exportSaft() {
		if (!$this->aauth->premission(119) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->helper('download');
    	$data = [
            'version' => $this->input->post('version'),
            'pay_acc' => $this->input->post('pay_acc'),
            'sdate' => $this->input->post('sdate'),
            'edate' => $this->input->post('edate')
    	];
        //var_dump($this->download);die;
    	$result = $this->export_model->xmldata($data);
    	if(!empty($result)){
			$name = 'saft'.'_'.$data['sdate'].'_'.$data['edate'].'.xml';
			force_download($name, $result);
        }
        else {
			 //echo "<script>alert('Work is under process!');</script>";
			echo "<script>alert('Data not found between ".$data['sdate']." to ".$data['edate']." range.! ');</script>";
			$this->tax_authority();
		}
    }


    // this is the funtion for the download xml file by eyno

	function tax_authority()
    {
		if (!$this->aauth->premission(119) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
		$this->li_a = 'taxstsaft';
        $head['title'] = "Saft Export";
        $head['usernm'] = $this->aauth->get_user()->username;
		$this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('export/saft_export', $data);
        $this->load->view('fixed/footer');
    }

    function crm()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $head['title'] = "Export CRM Data";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/crm');
        $this->load->view('fixed/footer');
    }

    function crm_now()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $type = $this->input->post('type');
        switch ($type) {
            case 1 :
                $this->customers();
                break;
            case 2 :
                $this->suppliers();
                break;
        }
    }


    private function customers()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=customers_' . $this->date . '..csv');
        header('Content-Transfer-Encoding: binary');
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = " WHERE loc='" . $this->aauth->get_user()->loc . "';";
        } elseif (!BDATA) {
            $whr = " WHERE loc='0';";
        }
        $query = $this->db->query("SELECT name,address,city,region,country,postbox,email,phone,company FROM geopos_customers $whr");
        echo "\xEF\xBB\xBF"; // Byte Order Mark
        echo $this->dbutil->csv_from_result($query);
        //  force_download('customers_' . $this->date . '.csv', );
    }


    private function suppliers()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = " WHERE loc='" . $this->aauth->get_user()->loc . "';";
        } elseif (!BDATA) {
            $whr = " WHERE loc='0';";
        }

        $query = $this->db->query("SELECT name,address,city,region,country,postbox,email,phone,company FROM geopos_supplier $whr");
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=suppliers_' . $this->date . '..csv');
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF"; // Byte Order Mark
        echo $this->dbutil->csv_from_result($query);
    }

    function transactions()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Export Transactions";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/transactions', $data);
        $this->load->view('fixed/footer');
    }

    function transactions_o()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }

        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = " AND loc='" . $this->aauth->get_user()->loc . "';";
        } elseif (!BDATA) {
            $whr = " AND loc='0';";
        }
        $pay_acc = $this->input->post('pay_acc');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        if ($pay_acc == 'All') {
            if ($trans_type == 'All') {
                $where = " WHERE (DATE(date) BETWEEN '$sdate' AND '$edate') ";
            } else {
                $where = " WHERE (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
            }
        } else {
            if ($trans_type == 'All') {
                $where = " WHERE acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') ";
            } else {
                $where = " WHERE acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
            }
        }
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=transactions_' . $this->date . '..csv');
        header('Content-Transfer-Encoding: binary');
        $query = $this->db->query("SELECT account,type,cat AS category,debit,credit,payer,method,date,note FROM geopos_transactions" . $where . ' ' . $whr);
        echo "\xEF\xBB\xBF"; // Byte Order Mark
        echo $this->dbutil->csv_from_result($query);
    }

    function products()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $head['title'] = "Export Products";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/products');
        $this->load->view('fixed/footer');
    }
	

    function products_o()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = "LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id WHERE geopos_warehouse.loc='" . $this->aauth->get_user()->loc . "';";
        } elseif (!BDATA) {
            $whr = "LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id WHERE geopos_warehouse.loc='0';";
        }
        $type = $this->input->post('type');
        $query = '';
        switch ($type) {
            case 1 :
                $query = "SELECT product_name,product_code,product_price,fproduct_price AS factory_price,taxrate,disrate AS discount_rate,qty FROM geopos_products $whr";
                break;
            case 2 :
                $query = "SELECT geopos_product_cat.title as category,geopos_products.product_name,geopos_products.product_code,geopos_products.product_price,geopos_products.fproduct_price AS factory_price,geopos_products.taxrate,geopos_products.disrate AS discount_rate,geopos_products.qty FROM geopos_products LEFT JOIN geopos_product_cat ON geopos_products.pcat=geopos_product_cat.id $whr";
                break;
        }
        $query = $this->db->query($query);
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=transactions_' . $this->date . '..csv');
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF"; // Byte Order Mark
        echo $this->dbutil->csv_from_result($query);
    }

    function account()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('transactions_model');
        $this->load->model('employee_model');
        $data['cat'] = $this->transactions_model->categories();
        $data['emp'] = $this->employee_model->list_employee();
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Export Transactions";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/account', $data);
        $this->load->view('fixed/footer');
    }

    function accounts_o()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('accounts_model');
        $pay_acc = $this->input->post('pay_acc');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['account'] = $this->accounts_model->details($pay_acc);
        $data['list'] = $this->reports_model->get_statements($pay_acc, $trans_type, $sdate, $edate);
        $data['lang']['statement'] = $this->lang->line('Account Statement');
        $data['lang']['title'] = $this->lang->line('Account');
        $data['lang']['var1'] = $data['account']['holder'];
        $data['lang']['var2'] = $data['account']['acn'];
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid']) $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('accounts/statementpdf-' . LTR, $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $pay_acc . '.pdf', 'D');
    }


    function employee()
    {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('accounts_model');
        $pay_acc = $this->input->post('employee');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $this->load->model('employee_model');
        $data['employee'] = $this->employee_model->employee_details($pay_acc);
        $data['list'] = $this->reports_model->get_statements_employee($pay_acc, $trans_type, $sdate, $edate);
        $data['lang']['statement'] = $this->lang->line('Employee Account Statement');
        $data['lang']['title'] = $this->lang->line('Employee');
        $data['lang']['var1'] = $data['employee']['name'];
        $data['lang']['var2'] = $data['employee']['email'];
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid']) $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('accounts/statementpdf-' . LTR, $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $pay_acc . '.pdf', 'D');
    }

    function employee_salary() {
		if (!$this->aauth->premission(102) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('settings_model', 'system');
        $this->load->model('accounts_model');
        $this->load->model('salaryprocess_model');
        $this->load->model('employee_model');
        $id = $this->input->get('id');
        $system = $this->system->company_details(1);
        $process = $this->salaryprocess_model->get_process_by_id($id);
        $data['lang']['ss_taxe'] = $system['social_security'];
        $data['process'] = $process;
        $data['time_course'] = $process['year'] . '/' . $process['month'];
        $data['employee'] = $this->employee_model->employee_details($process['employee_id']);
        $data['list'] = $this->reports_model->get_statements_employee($pay_acc, $trans_type, $sdate, $edate);
        $data['lang']['statement'] = $this->lang->line('Employee Account Statement');
        $data['lang']['title'] = $this->lang->line('Employee');
        $ccgrossi = $this->employee_model->get_gross_month($process['year'], $process['employee_id'], 1);
        $ccgrossinco = $ccgrossi['total'];
        $data['lang']['bcgrossincom'] = $ccgrossinco;
        $ccgrossss = $this->employee_model->get_gross_month($process['year'], $process['employee_id'], 3);
        $ccgrosstotss = $ccgrossss['total'];
        $data['lang']['bctotss'] = $ccgrosstotss;
        $ccgrossirs = $this->employee_model->get_gross_month($process['year'], $process['employee_id'], 4);
        $ccgrosstotirs = $ccgrossirs['total'];
        $data['lang']['bctotirs'] = $ccgrosstotirs;
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid'])
            $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('employee/salary_pdf', $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Salary_' . $employee['name'] . '_' . $data['time_course'] . '.pdf', 'I');
    }

    function trans_cat() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('transactions_model');
        $pay_cat = $this->input->post('pay_cat', true);
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['cat'] = $this->transactions_model->cat_details_name($pay_cat);
        $data['list'] = $this->reports_model->get_statements_cat($pay_cat, $trans_type, $sdate, $edate);
        $data['lang']['statement'] = $this->lang->line('Transaction Categories Statement');
        $data['lang']['title'] = $this->lang->line('Transaction Categories');
        $data['lang']['var1'] = $data['cat'] ['name'];
        $data['lang']['var2'] = '';
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid'])
            $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('accounts/statementpdf-' . LTR, $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $data['lang']['var1'] . '.pdf', 'D');
    }

    function customer() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('customers_model');
        $customer = $this->input->post('customer');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['customer'] = $this->customers_model->details($customer);
        $data['list'] = $this->reports_model->get_customer_statements($customer, $trans_type, $sdate, $edate);
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid'])
            $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('customers/statementpdf', $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $customer . '.pdf', 'D');
    }

    function supplier() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('supplier_model');
        $customer = $this->input->post('supplier');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['customer'] = $this->supplier_model->details($customer);
        $data['list'] = $this->reports_model->get_supplier_statements($customer, $trans_type, $sdate, $edate);
        $loc = location($this->aauth->get_user()->loc);
        $company = '<strong>' . $loc['cname'] . '</strong><br>' . $loc['address'] . '<br>' . $loc['city'] . ', ' . $loc['region'] . '<br>' . $loc['country'] . ' -  ' . $loc['postbox'] . '<br>' . $this->lang->line('Phone') . ': ' . $loc['phone'] . '<br> ' . $this->lang->line('Email') . ': ' . $loc['email'];
        if ($loc['taxid'])
            $company .= '<br>' . $this->lang->line('Tax') . ' ID: ' . $loc['taxid'];
        $data['lang']['company'] = $company;
        $html = $this->load->view('supplier/statementpdf', $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $customer . '.pdf', 'D');
    }

    function taxstatement() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->li_a = 'taxstsaft';
        $head['title'] = "Export TAX Report";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/taxstatement');
        $this->load->view('fixed/footer');
    }

    function taxstatement_o() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $whr = '';
        $whr2 = '';
        if ($this->aauth->get_user()->loc) {
            $whr = " AND geopos_invoices.loc='" . $this->aauth->get_user()->loc . "';";
            $whr2 = " AND geopos_purchase.loc='" . $this->aauth->get_user()->loc . "';";
        } elseif (!BDATA) {
            $whr = " AND geopos_invoices.loc='0';";
            $whr2 = " AND geopos_purchase.loc='0';";
        }
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $trans_type = $this->input->post('ty');
        $prefix = $this->config->item('prefix') . '-';
        $curr = $this->config->item('currency') . ' ';
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        //echo "\xEF\xBB\xBF"; // Byte Order Mark
        if ($trans_type == 'Sales') {
            header('Content-Disposition: attachment; filename=tax_transactions_SALES' . $this->date . '..csv');
            $where = " WHERE (DATE(geopos_invoices.invoicedate) BETWEEN '$sdate' AND '$edate') $whr";
            $query = $this->db->query("SELECT geopos_customers.taxid AS TAX_Number,concat('$prefix',geopos_invoices.tid) AS invoice_number,concat('$curr',geopos_invoices.total) AS amount,geopos_invoices.shipping AS shipping,geopos_invoices.ship_tax AS ship_tax,geopos_invoices.ship_tax_type AS ship_tax_type,geopos_invoices.discount AS discount,geopos_invoices.tax AS tax,geopos_invoices.pmethod AS payment_method,geopos_invoices.status AS status,geopos_invoices.refer AS referance,geopos_customers.name AS customer_name,geopos_customers.company AS Company_Name,geopos_invoices.invoicedate AS date FROM geopos_invoices LEFT JOIN geopos_customers ON geopos_invoices.csd=geopos_customers.id" . $where);
            echo $this->dbutil->csv_from_result($query);
        } else {
            header('Content-Disposition: attachment; filename=tax_transactions_PURCH' . $this->date . '..csv');
            $where = " WHERE (DATE(geopos_purchase.invoicedate) BETWEEN '$sdate' AND '$edate') $whr";
            $query = $this->db->query("SELECT concat('$prefix',geopos_purchase.tid) AS receipt_number,concat('$curr',geopos_purchase.total) AS amount,geopos_purchase.tax AS tax,geopos_supplier.name AS supplier_name,geopos_supplier.company AS Company_Name,geopos_purchase.invoicedate AS date FROM geopos_purchase LEFT JOIN geopos_supplier ON geopos_purchase.csd=geopos_supplier.id" . $where);
            echo $this->dbutil->csv_from_result($query);
        }
    }

    function people_products() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Export Product Transactions";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('export/product', $data);
        $this->load->view('fixed/footer');
    }

    function cust_products_o() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('customers_model');
        $customer = $this->input->post('customer');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['customer'] = $this->customers_model->details($customer);
        $data['list'] = $this->reports_model->product_customer_statements($customer, $sdate, $edate);
        $html = $this->load->view('customers/cust_prod_pdf', $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $customer . '.pdf', 'D');
    }

    function sup_products_o() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->load->model('reports_model');
        $this->load->model('supplier_model');
        $customer = $this->input->post('supplier');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $data['customer'] = $this->supplier_model->details($customer);
        $data['list'] = $this->reports_model->product_supplier_statements($customer, $sdate, $edate);
        $html = $this->load->view('supplier/supp_prod_pdf', $data, true);
        ini_set('memory_limit', '64M');
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html);
        $pdf->Output('Statement' . $customer . '.pdf', 'I');
    }


	function tax_authority() {
		if (!$this->aauth->premission(118) && !$this->aauth->get_user()->roleid == 5 && !$this->aauth->get_user()->roleid == 7) {
            exit($this->lang->line('translate19'));
        }
        $this->li_a = 'taxstsaft';
        $head['title'] = "Saft Export";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('locations_model');
        $data['activation'] = $this->export_model->getactivateVal($this->aauth->get_user()->loc);
		$data['activation_caixa'] = $this->export_model->getactivateValCaixa($this->aauth->get_user()->loc);
        $data['locations'] = $this->locations_model->locations_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('export/saft_export', $data);
        $this->load->view('fixed/footer');
    }

    public function save_val_ativate()
    {
		$bill_doc = 0;
		$trans_doc = 0;
		
		if(filter_has_var(INPUT_POST,'billing_doc')) {
			$bill_doc = 1;
		}else{
			$bill_doc = 0;
		}
		
		if(filter_has_var(INPUT_POST,'transport_doc')) {
			$trans_doc = 1;
		}else{
			$trans_doc = 0;
		}
		
		
		$databdtrans = "";
		if($trans_doc == 1)
		{
			if($this->input->post('transport_doc_date') == "")
			{
				$databdtrans = date('d-m-Y');
			}else{
				$databdtrans = $this->input->post('transport_doc_date');
			}
		}
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $date = $this->input->post('adate');
		$id_saft_activ = $this->input->post('id_saft_activ');
        $result = $this->export_model->addActivateVal($id_saft_activ, $bill_doc, $trans_doc, $username, $password, $date, $this->aauth->get_user()->loc, $databdtrans);
        if ($result) {
            echo json_encode(array('status' => 'Success', 'message' => 'Campos de ativação de comunicação á AT alterados com sucesso!'));
        }else{
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

	
	public function save_val_ativate_caixa()
    {
		$caixa_1 = 0;
		$caixa_2 = 0;
		$caixa_3 = 0;
		$caixa_4 = 0;
		
		if(filter_has_var(INPUT_POST,'caixa_1')) {
			$caixa_1 = 1;
		}
		
		if(filter_has_var(INPUT_POST,'caixa_2')) {
			$caixa_2 = 1;
		}
		
		if(filter_has_var(INPUT_POST,'caixa_3')) {
			$caixa_3 = 1;
		}
		
		if(filter_has_var(INPUT_POST,'caixa_4')) {
			$caixa_4 = 1;
		}
		
		$dateActiv = "";
		if($caixa_1 == 1 && $caixa_2 == 1 && $caixa_3 == 1)
		{
			if($caixa_4 == 1){
				if($this->input->post('caixa_doc_date') == "")
				{
					$dateActiv = date('d-m-Y');
				}else{
					$dateActiv = $this->input->post('caixa_doc_date');
				}
			}
		}
		$caixa_id_saft = $this->input->post('caixa_id_saft');
        $result = $this->export_model->addActivateValCaixa($caixa_id_saft, $caixa_1, $caixa_2, $caixa_3, $caixa_4, $this->aauth->get_user()->loc, $dateActiv);
        if ($result) {
            echo json_encode(array('status' => 'Success', 'message' => 'Regime de IVA alterado com Sucesso!'));
        }else{
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }
}
