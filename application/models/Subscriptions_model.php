<?php
/**

 * 
 * ***********************************************************************
 *
 
 
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * 
 *  * 
 * ***********************************************************************
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Subscriptions_model extends CI_Model
{
    var $table = 'geopos_invoices';
    var $column_order = array(null, 'geopos_series.serie AS serie_name', 'geopos_invoices.tid', 'geopos_customers.name', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status', null, null);
    var $column_search = array('geopos_series.serie', 'geopos_invoices.tid', 'geopos_customers.name', 'geopos_invoices.invoicedate', 'geopos_invoices.total','geopos_invoices.status');
    var $order = array('tid' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    public function invoice_details2($id, $eid = '',$typ = 0,$p=true)
    {
		$this->db->select("geopos_draft.*,
			geopos_data_transport.autoid,
			geopos_data_transport.expedition,
			geopos_data_transport.exp_date,
			geopos_data_transport.exp_mat,
			geopos_data_transport.exp_des,
			geopos_data_transport.charge_address,
			geopos_data_transport.charge_postbox,geopos_data_transport.charge_city,geopos_data_transport.charge_country,geopos_data_transport.discharge_address,
			geopos_data_transport.discharge_postbox,
			geopos_data_transport.discharge_city,
			geopos_data_transport.discharge_country, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name,
			geopos_assets.assest_name as autoid_name,l1.name as charge_country_name, l2.name as discharge_country_name, c1.val2 as expd_t, c1.val1 as expd_name, 
			geopos_draft.id as iddoc, CASE WHEN geopos_locations.address = '' OR geopos_locations.address IS NULL THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
			, CASE WHEN geopos_locations.postbox = '' OR geopos_locations.postbox IS NULL THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
			, CASE WHEN geopos_locations.city = '' OR geopos_locations.city IS NULL THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
			, CASE WHEN geopos_locations.country = '' OR geopos_locations.country IS NULL THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
			CASE WHEN geopos_locations.taxid = '' OR geopos_locations.taxid IS NULL THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
			CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
				THEN 
					CASE WHEN w2.title is null or w2.title = '' 
						THEN 'Todos' 
					ELSE w2.title 
					END 
				ELSE w1.title 
				END AS loc_cname,
			geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
			SUM(geopos_draft.shipping + geopos_draft.ship_tax) AS shipping,geopos_customers.*,geopos_draft.loc as loc,geopos_draft.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
		$this->db->from('geopos_draft');
		$this->db->join('geopos_data_transport', 'geopos_draft.id=geopos_data_transport.tid', 'left');
		$this->db->join('geopos_assets', 'geopos_data_transport.autoid = geopos_assets.id', 'left');
		$this->db->join('geopos_countrys as l1', 'geopos_data_transport.charge_country = l1.prefix', 'left');
		$this->db->join('geopos_countrys as l2', 'geopos_data_transport.discharge_country = l2.prefix', 'left');
		$this->db->join('geopos_config as c1', 'c1.id = geopos_data_transport.expedition', 'left');
		$this->db->join('geopos_accounts', "geopos_accounts.loc = geopos_draft.loc and geopos_accounts.payonline = 'Yes'", 'left');
		$this->db->join('geopos_system', 'geopos_system.id = 1', 'left');
		$this->db->join('geopos_locations', 'geopos_locations.id = geopos_draft.loc', 'left');
		$this->db->join('geopos_warehouse as w1', 'geopos_locations.ware=w1.id', 'left');
		$this->db->join('geopos_warehouse as w2', 'geopos_draft.loc=w2.id', 'left');
		$this->db->join('geopos_currencies', 'geopos_currencies.id = geopos_draft.multi', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_draft.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_documents_copys', 'geopos_draft.loc = geopos_documents_copys.loc and geopos_draft.irs_type = geopos_documents_copys.typ_doc', 'inner');
		$this->db->join('geopos_config as copys', 'geopos_documents_copys.copy = copys.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_draft.serie', 'left');
		if($typ == 0)
		{
			$this->db->join('geopos_customers', 'geopos_draft.csd = geopos_customers.id', 'left');
		}else{
			$this->db->join('geopos_supplier', 'geopos_supplier_notes.csd = geopos_supplier.id', 'left');
		}
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_draft.term', 'left');
		$this->db->where('geopos_series.predf', 1);
		$this->db->where('geopos_draft.ext', $typ);
        $this->db->where('geopos_draft.id', $id);
        if ($eid) {
            $this->db->where('geopos_draft.eid', $eid);
        }
        if($p) {
            if ($this->aauth->get_user()->loc ) {
                $this->db->where('geopos_draft.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_draft.loc', 0);
            }
        }
        $query = $this->db->get();
        return $query->row_array();
    }


    public function invoice_details($id, $eid = '',$typ = 0,$p=true)
    {
		if($this->aauth->get_user()->loc == 0)
		{
			if($typ == 0){
				$this->db->select("geopos_invoices.*,
					geopos_data_transport.autoid,
					geopos_data_transport.expedition,
					geopos_data_transport.exp_date,
					geopos_data_transport.exp_mat,
					geopos_data_transport.exp_des,
					geopos_data_transport.charge_address,
					geopos_data_transport.charge_postbox,geopos_data_transport.charge_city,geopos_data_transport.charge_country,geopos_data_transport.discharge_address,
					geopos_data_transport.discharge_postbox,
					geopos_data_transport.discharge_city,
					geopos_data_transport.discharge_country, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name,
					geopos_assets.assest_name as autoid_name,l1.name as charge_country_name, l2.name as discharge_country_name, c1.val2 as expd_t, c1.val1 as expd_name, 
					geopos_invoices.id as iddoc, 
					, CASE WHEN geopos_locations.address = '' OR geopos_locations.address IS NULL THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
					, CASE WHEN geopos_locations.postbox = '' OR geopos_locations.postbox IS NULL THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
					, CASE WHEN geopos_locations.city = '' OR geopos_locations.city IS NULL THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
					, CASE WHEN geopos_locations.country = '' OR geopos_locations.country IS NULL THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
					CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
					THEN 
						CASE WHEN w2.title is null or w2.title = '' 
							THEN 'Todos' 
						ELSE w2.title 
						END 
					ELSE w1.title 
					END AS loc_cname,
					CASE WHEN geopos_locations.taxid = '' OR geopos_locations.taxid IS NULL THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
					geopos_system.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, 
					CASE WHEN geopos_accounts.acn = '' OR geopos_accounts.acn IS NULL THEN 'Sem Informação Disponível' ELSE geopos_accounts.acn END as loc_contabancaria,
					geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
					SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_customers.*,
					copys.val2 as numcop,
					geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
			}else{
				$this->db->select("geopos_invoices.*,
					geopos_data_transport.autoid,
					geopos_data_transport.expedition,
					geopos_data_transport.exp_date,
					geopos_data_transport.exp_mat,
					geopos_data_transport.exp_des,
					geopos_data_transport.charge_address,
					geopos_data_transport.charge_postbox,geopos_data_transport.charge_city,geopos_data_transport.charge_country,geopos_data_transport.discharge_address,
					geopos_data_transport.discharge_postbox,
					geopos_data_transport.discharge_city,
					geopos_data_transport.discharge_country, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name,
					geopos_assets.assest_name as autoid_name,l1.name as charge_country_name, l2.name as discharge_country_name, c1.val2 as expd_t, c1.val1 as expd_name, 
					geopos_invoices.id as iddoc, 
					, CASE WHEN geopos_locations.address = '' OR geopos_locations.address IS NULL THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
					, CASE WHEN geopos_locations.postbox = '' OR geopos_locations.postbox IS NULL THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
					, CASE WHEN geopos_locations.city = '' OR geopos_locations.city IS NULL THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
					, CASE WHEN geopos_locations.country = '' OR geopos_locations.country IS NULL THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
					CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
					THEN 
						CASE WHEN w2.title is null or w2.title = '' 
							THEN 'Todos' 
						ELSE w2.title 
						END 
					ELSE w1.title 
					END AS loc_cname,
					CASE WHEN geopos_locations.taxid = '' OR geopos_locations.taxid IS NULL THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
					geopos_system.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, 
					CASE WHEN geopos_accounts.acn = '' OR geopos_accounts.acn IS NULL THEN 'Sem Informação Disponível' ELSE geopos_accounts.acn END as loc_contabancaria,
					geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
					SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_supplier.*,
					copys.val2 as numcop,
					geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_supplier.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
			}
		}else{
			if($typ == 0){
				$this->db->select("geopos_invoices.*,
					geopos_data_transport.autoid,
					geopos_data_transport.expedition,
					geopos_data_transport.exp_date,
					geopos_data_transport.exp_mat,
					geopos_data_transport.exp_des,
					geopos_data_transport.charge_address,
					geopos_data_transport.charge_postbox,geopos_data_transport.charge_city,geopos_data_transport.charge_country,geopos_data_transport.discharge_address,
					geopos_data_transport.discharge_postbox,
					geopos_data_transport.discharge_city,
					geopos_data_transport.discharge_country, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name, 
					geopos_invoices.id as iddoc, 
					, CASE WHEN geopos_locations.address = '' OR geopos_locations.address IS NULL THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
					, CASE WHEN geopos_locations.postbox = '' OR geopos_locations.postbox IS NULL THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
					, CASE WHEN geopos_locations.city = '' OR geopos_locations.city IS NULL THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
					, CASE WHEN geopos_locations.country = '' OR geopos_locations.country IS NULL THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
					CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
					THEN 
						CASE WHEN w2.title is null or w2.title = '' 
							THEN 'Todos' 
						ELSE w2.title 
						END 
					ELSE w1.title 
					END AS loc_cname,
					CASE WHEN geopos_locations.taxid = '' OR geopos_locations.taxid IS NULL THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
					geopos_locations.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, 
					CASE WHEN geopos_accounts.acn = '' OR geopos_accounts.acn IS NULL THEN 'Sem Informação Disponível' ELSE geopos_accounts.acn END as loc_contabancaria,
					geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
					SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_customers.*,
					copys.val2 as numcop,
					geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
			}else{
				$this->db->select("geopos_invoices.*,
					geopos_data_transport.autoid,
					geopos_data_transport.expedition,
					geopos_data_transport.exp_date,
					geopos_data_transport.exp_mat,
					geopos_data_transport.exp_des,
					geopos_data_transport.charge_address,
					geopos_data_transport.charge_postbox,geopos_data_transport.charge_city,geopos_data_transport.charge_country,geopos_data_transport.discharge_address,
					geopos_data_transport.discharge_postbox,
					geopos_data_transport.discharge_city,
					geopos_data_transport.discharge_country, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name, 
					geopos_invoices.id as iddoc, 
					, CASE WHEN geopos_locations.address = '' OR geopos_locations.address IS NULL THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
					, CASE WHEN geopos_locations.postbox = '' OR geopos_locations.postbox IS NULL THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
					, CASE WHEN geopos_locations.city = '' OR geopos_locations.city IS NULL THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
					, CASE WHEN geopos_locations.country = '' OR geopos_locations.country IS NULL THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
					CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
					THEN 
						CASE WHEN w2.title is null or w2.title = '' 
							THEN 'Todos' 
						ELSE w2.title 
						END 
					ELSE w1.title 
					END AS loc_cname,
					CASE WHEN geopos_locations.taxid = '' OR geopos_locations.taxid IS NULL THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
					geopos_locations.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, 
					CASE WHEN geopos_accounts.acn = '' OR geopos_accounts.acn IS NULL THEN 'Sem Informação Disponível' ELSE geopos_accounts.acn END as loc_contabancaria,
					geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
					SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_supplier.*,
					copys.val2 as numcop,
					geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_supplier.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
			}
		}
        
        $this->db->from($this->table);
		$this->db->join('geopos_data_transport', 'geopos_invoices.id=geopos_data_transport.tid', 'left');
		$this->db->join('geopos_assets', 'geopos_data_transport.autoid = geopos_assets.id', 'left');
		$this->db->join('geopos_countrys as l1', 'geopos_data_transport.charge_country = l1.prefix', 'left');
		$this->db->join('geopos_countrys as l2', 'geopos_data_transport.discharge_country = l2.prefix', 'left');
		$this->db->join('geopos_config as c1', 'c1.id = geopos_data_transport.expedition', 'left');
		$this->db->join('geopos_accounts', "geopos_accounts.loc = geopos_invoices.loc and geopos_accounts.payonline = 'Yes'", 'left');
		$this->db->join('geopos_system', 'geopos_system.id = 1', 'left');
		$this->db->join('geopos_locations', 'geopos_locations.id = geopos_invoices.loc', 'left');
		$this->db->join('geopos_warehouse as w1', 'geopos_locations.ware=w1.id', 'left');
		$this->db->join('geopos_warehouse as w2', 'geopos_invoices.loc=w2.id', 'left');
		$this->db->join('geopos_currencies', 'geopos_currencies.id = geopos_invoices.multi', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_invoices.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_documents_copys', 'geopos_invoices.loc = geopos_documents_copys.loc and geopos_invoices.irs_type = geopos_documents_copys.typ_doc', 'inner');
		$this->db->join('geopos_config as copys', 'geopos_documents_copys.copy = copys.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_invoices.serie', 'left');
		$this->db->join('geopos_terms', 'geopos_terms.id = geopos_invoices.term', 'left');
		if($typ == 0){
			$this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
		}else{
			$this->db->join('geopos_supplier', 'geopos_invoices.csd = geopos_supplier.id', 'left');
		}
		$this->db->where('geopos_invoices.ext', $typ);
        $this->db->where('geopos_invoices.id', $id);
		$this->db->where('geopos_series.predf', 1);
        if ($eid) {
            $this->db->where('geopos_invoices.eid', $eid);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function invoice_products($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_invoice_items');
        $this->db->where('tid', $id);
        $query = $this->db->get();
        return $query->result_array();

    }

	public function invoice_products2($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_draft_items');
        $this->db->where('tid', $id);
        $query = $this->db->get();
        return $query->result_array();

    }
	
    public function items_with_product($id)
    {
        $this->db->select('geopos_invoice_items.*,geopos_products.qty AS alert');
        $this->db->from('geopos_invoice_items');
        $this->db->where('tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
        $query = $this->db->get();
        return $query->result_array();

    }
	
	public function items_with_product2($id)
    {
        $this->db->select('geopos_draft_items.*,geopos_products.qty AS alert');
        $this->db->from('geopos_draft_items');
        $this->db->where('tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_draft_items.pid', 'left');
        $query = $this->db->get();
        return $query->result_array();

    }

    public function currencies()
    {

        $this->db->select('*');
        $this->db->from('geopos_currencies');

        $query = $this->db->get();
        return $query->result_array();

    }

    public function currency_d($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_currencies');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function warehouses()
    {
        $this->db->select('*');
        $this->db->from('geopos_warehouse');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
          if(BDATA)  $this->db->or_where('loc', 0);
        }  elseif(!BDATA) { $this->db->where('loc', 0); }


        $query = $this->db->get();
        return $query->result_array();

    }

    public function invoice_transactions2($id)
    {
        $this->db->select("geopos_draft_payments.*,geopos_config.val1 AS methodname,geopos_config.val2 as datapay, '0.00' as debit, '0.00' as credit");
        $this->db->from('geopos_draft_payments');
		$this->db->join('geopos_config', 'geopos_draft_payments.pay_met = geopos_config.id', 'left');
        $this->db->where('geopos_draft_payments.tid', $id);
        $query = $this->db->get();
        return $query->result_array();

    }
	
    public function invoice_transactions($id)
    {
        $this->db->select('geopos_transactions.*, geopos_config.val1 as methodname, geopos_trans_cat.cod as cod_cat, geopos_trans_cat.name as name_cat');
        $this->db->from('geopos_transactions');
		$this->db->join('geopos_config', 'geopos_transactions.method = geopos_config.id', 'left');
		$this->db->join('geopos_trans_cat', 'geopos_transactions.cat = geopos_trans_cat.id', 'left');
        $this->db->where('geopos_transactions.tid', $id);
        $this->db->where('geopos_transactions.ext', 0);
        $query = $this->db->get();
        return $query->result_array();

    }

    public function invoice_delete($id, $eid = '')
    {

        $this->db->trans_start();

        $this->db->select('status');
        $this->db->from('geopos_invoices');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();

         if ($this->aauth->get_user()->loc) {
            if ($eid) {

                $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid, 'loc' => $this->aauth->get_user()->loc));


            } else {
                $res = $this->db->delete('geopos_invoices', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));
            }
        }

        else {
            if (BDATA) {
                if ($eid) {

                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid));


                } else {
                    $res = $this->db->delete('geopos_invoices', array('id' => $id));
                }
            } else {


                if ($eid) {

                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid, 'loc' => 0));


                } else {
                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'loc' => 0));
                }
            }
        }
        $affect = $this->db->affected_rows();


        if ($res) {
            if ($result['status'] != 'canceled') {
                $this->db->select('pid,qty');
                $this->db->from('geopos_invoice_items');
                $this->db->where('tid', $id);
                $query = $this->db->get();
                $prevresult = $query->result_array();

                foreach ($prevresult as $prd) {
                    $amt = $prd['qty'];
                    $this->db->set('qty', "qty+$amt", FALSE);
                    $this->db->where('pid', $prd['pid']);
                    $this->db->update('geopos_products');
                }
            }
            if ($affect) $this->db->delete('geopos_invoice_items', array('tid' => $id));
            $data = array('type' => 9, 'rid' => $id);
            $this->db->delete('geopos_metadata', $data);
            if ($this->db->trans_complete()) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function _get_datatables_query($opt = '')
    {
        $this->db->select("geopos_invoices.id, geopos_invoices.irs_type, geopos_irs_typ_doc.id as irs_type_c,geopos_series.serie AS serie_name,geopos_invoices.tid,geopos_invoices.invoicedate, geopos_customers.name, 
		geopos_customers.taxid, geopos_invoices.subtotal, geopos_invoices.tax, geopos_invoices.total, geopos_invoices.status, geopos_invoices.pamnt, 
		geopos_invoices.invoiceduedate, geopos_irs_typ_doc.type,geopos_invoices.i_class,
		geopos_invoices.ext,CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
			THEN 
				CASE WHEN w2.title is null or w2.title = '' 
					THEN 'Todos' 
				ELSE w2.title 
				END 
			ELSE w1.title 
			END AS loc_cname");
		
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class>', 1);
		$this->db->where('geopos_invoices.i_class<', 5);
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
		
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        }
        elseif(!BDATA) { 
			$this->db->where('geopos_invoices.loc', 0); 
		}
		
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }
		$this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_invoices.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_invoices.serie', 'left');
		$this->db->join('geopos_locations', 'geopos_locations.id = geopos_invoices.loc', 'left');
		$this->db->join('geopos_warehouse as w1', 'geopos_locations.ware=w1.id', 'left');
		$this->db->join('geopos_warehouse as w2', 'geopos_invoices.loc=w2.id', 'left');
		
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if ($this->input->post('search')['value']) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start();
                    $this->db->like($item, $this->input->post('search')['value']);
                } else {
                    $this->db->or_like($item, $this->input->post('search')['value']);
                }

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
	
	var $column_order2 = array(null, 'geopos_series.serie AS serie_name', 'geopos_draft.tid', 'geopos_customers.name', 'geopos_draft.invoicedate', null, 'geopos_draft.total', 'Rascunho as status', null);
    var $column_search2 = array('geopos_series.serie', 'geopos_draft.tid', 'geopos_customers.name', 'geopos_draft.invoicedate', 'geopos_draft.total');
    var $order2 = array('geopos_draft.tid' => 'DESC');
	private function _get_datatables_query2($opt = '')
    {
        $this->db->select("geopos_draft.id,geopos_series.serie AS serie_name,geopos_draft.tid,geopos_draft.invoicedate, geopos_customers.name, geopos_customers.taxid, geopos_draft.subtotal, geopos_draft.tax, geopos_draft.total, 'Rascunho as status', '0.00 as pamnt', geopos_draft.invoiceduedate, geopos_irs_typ_doc.type,
		geopos_draft.i_class,CASE WHEN geopos_locations.cname = '' OR geopos_locations.cname IS NULL 
			THEN 
				CASE WHEN w2.title is null or w2.title = '' 
					THEN 'Todos' 
				ELSE w2.title 
				END 
			ELSE w1.title 
			END AS loc_cname");
        $this->db->from('geopos_draft');
		$this->db->where('geopos_draft.i_class>', 1);
		$this->db->where('geopos_draft.i_class<', 5);
        if ($opt) {
            $this->db->where('geopos_draft.eid', $opt);
        }
		
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_draft.loc', $this->aauth->get_user()->loc);
        }
        elseif(!BDATA) { 
			$this->db->where('geopos_draft.loc', 0); 
		}
		
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_draft.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_draft.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }
        $this->db->join('geopos_customers', 'geopos_draft.csd=geopos_customers.id', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_draft.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_draft.serie', 'left');
		$this->db->join('geopos_locations', 'geopos_locations.id = geopos_draft.loc', 'left');
		$this->db->join('geopos_warehouse as w1', 'geopos_locations.ware=w1.id', 'left');
		$this->db->join('geopos_warehouse as w2', 'geopos_draft.loc=w2.id', 'left');
		
        $i = 0;

        foreach ($this->column_search2 as $item) // loop column
        {
            if ($this->input->post('search')['value']) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start();
                    $this->db->like($item, $this->input->post('search')['value']);
                } else {
                    $this->db->or_like($item, $this->input->post('search')['value']);
                }

                if (count($this->column_search2) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order2[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order2)) {
            $order = $this->order2;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
	
	function get_datatables2($opt = '')
    {
        $this->_get_datatables_query2($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_draft.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_draft.loc', 0); }

        return $query->result();
    }
	
	public function count_all2($opt = '')
    {
        $this->db->select('geopos_draft.id');
        $this->db->from('geopos_draft');
		$this->db->where('geopos_draft.i_class>', 1);
		$this->db->where('geopos_draft.i_class<', 5);
        if ($opt) {
            $this->db->where('geopos_draft.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_draft.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_draft.loc', 0); }
        return $this->db->count_all_results();
    }
	
	function count_filtered2($opt = '')
    {
        $this->_get_datatables_query2($opt);
        if ($opt) {
            $this->db->where('eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_draft.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_draft.loc', 0); }
        $query = $this->db->get();
        return $query->num_rows();
    }

    function get_datatables($opt = '')
    {
        $this->_get_datatables_query($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_invoices.loc', 0); }

        return $query->result();
    }

    function count_filtered($opt = '')
    {
        $this->_get_datatables_query($opt);
        if ($opt) {
            $this->db->where('eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_invoices.loc', 0); }
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($opt = '')
    {
        $this->db->select('geopos_invoices.id');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class>', 1);
		$this->db->where('geopos_invoices.i_class<', 5);
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);

        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_invoices.loc', 0); }
        return $this->db->count_all_results();
    }

    public function employee($id)
    {
        $this->db->select('geopos_employees.name,geopos_employees.sign,geopos_users.roleid,geopos_hrm.val1 as depart_employee');
        $this->db->from('geopos_employees');
        $this->db->where('geopos_employees.id', $id);
        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');
		$this->db->join('geopos_hrm', 'geopos_hrm.id = geopos_employees.dept', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function meta_insert($id, $type, $meta_data)
    {
        $data = array('type' => $type, 'rid' => $id, 'col1' => $meta_data);
        if ($id) {
            return $this->db->insert('geopos_metadata', $data);
        } else {
            return 0;
        }
    }

    public function attach($id)
    {
        $this->db->select('geopos_metadata.*');
        $this->db->from('geopos_metadata');
        $this->db->where('geopos_metadata.type', 1);
        $this->db->where('geopos_metadata.rid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function meta_delete($id, $type, $name)
    {
        if (@unlink(FCPATH . 'userfiles/attach/' . $name)) {
            return $this->db->delete('geopos_metadata', array('rid' => $id, 'type' => $type, 'col1' => $name));
        }
    }

    public function gateway_list($enable = '')
    {

        $this->db->from('geopos_gateways');
        if ($enable == 'Yes') {
            $this->db->where('enable', 'Yes');
        }
        $query = $this->db->get();
        return $query->result_array();
    }
}