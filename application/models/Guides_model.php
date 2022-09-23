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

class Guides_model extends CI_Model
{
	var $table = 'geopos_guides';
    var $column_order = array(null, 'geopos_guides.tid', 'geopos_customers.name', 'geopos_guides.invoicedate', 'geopos_guides.total','geopos_guides.status', null);
    var $column_search = array('geopos_guides.tid', 'geopos_customers.name', 'geopos_guides.invoicedate', 'geopos_guides.total','geopos_guides.status');
    var $order = array('geopos_guides.tid' => 'desc');


    public function __construct()
    {
        parent::__construct();
    }
	
	public function guide_autos_company()
    {
		$this->db->select("geopos_assets.*, CONCAT('(', geopos_assets.assest_name, ') - ', geopos_assets.product_des) AS nameasset");
        $this->db->from('geopos_assets');
		$this->db->join('geopos_assets_cat', 'geopos_assets_cat.id = geopos_assets.acat', 'left');
        $this->db->where('geopos_assets_cat.type', 1);
		if($this->aauth->get_user()->loc > 0)
		{
			$this->db->where('geopos_assets.warehouse', $this->aauth->get_user()->loc);
		}
		$query = $this->db->get();
		$result = $query->result_array();
		$assets_list = '';
		foreach ($result as $row) {
            $assets_list .= '<option value="' . $row['id'] . '">' . $row['nameasset'] . '</option> ';
        }
		return $assets_list;
	}
	
	public function guide_details($id, $eid = '',$p=true)
    {
		if($this->aauth->get_user()->loc == 0)
		{
			$this->db->select("geopos_guides.*, geopos_guides.id as iddoc, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name
			, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
			, CASE WHEN geopos_locations.postbox = '' THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
			, CASE WHEN geopos_locations.city = '' THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
			, CASE WHEN geopos_locations.country = '' THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
			CASE WHEN geopos_locations.cname = '' THEN geopos_system.cname ELSE geopos_locations.cname END AS loc_cname,
			CASE WHEN geopos_locations.taxid = '' THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
			geopos_system.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, geopos_bank_ac.acn as loc_contabancaria,
			geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
			geopos_config.val2 as expd_t, geopos_config.val1 as expd_name, geopos_assets.assest_name as autoid_name,l1.name as charge_country_name, l2.name as discharge_country_name,
			SUM(geopos_guides.shipping + geopos_guides.ship_tax) AS shipping,geopos_customers.*,
			geopos_guides.loc as loc,geopos_guides.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
		}else{
			$this->db->select("geopos_guides.*, geopos_guides.id as iddoc, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_name
			, CASE WHEN geopos_locations.address = '' THEN geopos_system.address ELSE geopos_locations.address END AS loc_adress
			, CASE WHEN geopos_locations.postbox = '' THEN geopos_system.postbox ELSE geopos_locations.postbox END AS loc_postbox
			, CASE WHEN geopos_locations.city = '' THEN geopos_system.city ELSE geopos_locations.city END AS loc_city
			, CASE WHEN geopos_locations.country = '' THEN geopos_system.country ELSE geopos_locations.country END AS loc_country,
			CASE WHEN geopos_locations.cname = '' THEN geopos_system.cname ELSE geopos_locations.cname END AS loc_cname,
			CASE WHEN geopos_locations.taxid = '' THEN geopos_system.taxid ELSE geopos_locations.taxid END AS loc_taxid,
			geopos_locations.zon_fis AS loc_zon_fis, geopos_system.certification as loc_certification, geopos_bank_ac.acn as loc_contabancaria,
			geopos_currencies.code as multiname, geopos_series.serie AS serie_name, geopos_series.atc AS atc_serie, geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n, 
			geopos_config.val2 as expd_t, geopos_config.val1 as expd_name, geopos_assets.assest_name as autoid_name,l1.name as charge_country_name, l2.name as discharge_country_name,
			SUM(geopos_guides.shipping + geopos_guides.ship_tax) AS shipping,geopos_customers.*,
			geopos_guides.loc as loc,geopos_guides.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms");
		}
		$this->db->from($this->table);
        $this->db->where('geopos_guides.id', $id);
        if ($eid) {
            $this->db->where('geopos_guides.eid', $eid);
        }
        if($p) {
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_guides.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_guides.loc', 0);
            }
        }
		
		$this->db->join('geopos_assets', 'geopos_guides.autoid = geopos_assets.id', 'left');
		$this->db->join('geopos_countrys as l1', 'geopos_guides.charge_country = l1.prefix', 'left');
		$this->db->join('geopos_countrys as l2', 'geopos_guides.discharge_country = l2.prefix', 'left');
		$this->db->join('geopos_bank_ac', 'geopos_bank_ac.id = 1', 'left');
		$this->db->join('geopos_system', 'geopos_system.id = 1', 'left');
		$this->db->join('geopos_locations', 'geopos_locations.id = geopos_guides.loc', 'left');
		$this->db->join('geopos_currencies', 'geopos_currencies.id = geopos_guides.multi', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_guides.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_guides.serie', 'left');
		$this->db->join('geopos_config', 'geopos_config.id = geopos_guides.expedition', 'left');
        $this->db->join('geopos_customers', 'geopos_guides.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_guides.term', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function guide_products($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_guides_items');
        $this->db->where('tid', $id);
        $query = $this->db->get();
        return $query->result_array();

    }

    public function items_with_product($id)
    {
        $this->db->select('geopos_guides_items.*,geopos_products.qty AS alert');
        $this->db->from('geopos_guides_items');
        $this->db->where('tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_guides_items.pid', 'left');
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

    public function currency_d($id, $loc = 0)
    {
        if ($loc) {
            $query = $this->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
            $row = $query->row_array();
            $id = $row['cur'];
        }
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

    public function guide_delete($id, $eid = '')
    {
        $this->db->trans_start();
        $this->db->select('tid,total');
        $this->db->from('geopos_guides');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        if ($this->aauth->get_user()->loc) {
            if ($eid) {

                $res = $this->db->delete('geopos_guides', array('id' => $id, 'eid' => $eid, 'loc' => $this->aauth->get_user()->loc));


            } else {
                $res = $this->db->delete('geopos_guides', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));
            }
        }

        else {
            if (BDATA) {
                if ($eid) {

                    $res = $this->db->delete('geopos_guides', array('id' => $id, 'eid' => $eid));


                } else {
                    $res = $this->db->delete('geopos_guides', array('id' => $id));
                }
            } else {


                if ($eid) {

                    $res = $this->db->delete('geopos_guides', array('id' => $id, 'eid' => $eid, 'loc' => 0));


                } else {
                    $res = $this->db->delete('geopos_guides', array('id' => $id, 'loc' => 0));
                }
            }
        }

        $affect = $this->db->affected_rows();

        if ($res) {
            if ($result['status'] != 'canceled') {
                $this->db->select('pid,qty');
                $this->db->from('geopos_guides_items');
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


            if ($affect) $this->db->delete('geopos_guides_items', array('tid' => $id));

            $data = array('type' => 9, 'rid' => $id);
            $this->db->delete('geopos_metadata', $data);

            $alert= $this->custom->api_config(66);
            if ($alert['method'] == 1) {
                 $this->load->model('communication_model');
                 $subject= $result['tid'].' '. $this->lang->line('DELETED');
                 $body=$subject.'<br> '. $this->lang->line('Amount').' '. $result['total'].'<br> '. $this->lang->line('Employee').' '. $this->aauth->get_user()->username.'<br> ID# '. $result['tid'];
               $out= $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
            }

            if ($this->db->trans_complete()) {
                return true;
            } else {
                return false;
            }
        }

    }


    private function _get_datatables_query($opt = '', $typ = 1)
    {
        $this->db->select('geopos_guides.id,geopos_guides.status,geopos_guides.tid,geopos_guides.typeguide,geopos_guides.invoicedate,geopos_guides.total,geopos_assets.assest_name as viatura,geopos_customers.name, geopos_irs_typ_doc.type');
        $this->db->from($this->table);
        $this->db->where('geopos_guides.i_class', 0);
		$this->db->where('geopos_guides.typeguide', $typ);
        if ($opt) {
            $this->db->where('geopos_guides.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_guides.loc', $this->aauth->get_user()->loc);
        }
        elseif(!BDATA) { $this->db->where('geopos_guides.loc', 0); }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_guides.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_guides.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }
        $this->db->join('geopos_customers', 'geopos_guides.csd=geopos_customers.id', 'left');
		$this->db->join('geopos_assets', 'geopos_guides.autoid=geopos_assets.id', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_guides.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_guides.serie', 'left');
		
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if ($this->input->post('search')['value']) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
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

    function get_datatables($opt = '', $typ = 1)
    {
        $this->_get_datatables_query($opt, $typ);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_guides.i_class', 0);
		$this->db->where('geopos_guides.typeguide', $typ);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_guides.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_guides.loc', 0); }
        return $query->result();
    }

    function count_filtered($opt = '', $typ = 1)
    {
        $this->_get_datatables_query($opt, $typ);
        if ($opt) {
            $this->db->where('eid', $opt);
        }
		$this->db->where('geopos_guides.typeguide', $typ);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_guides.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_guides.loc', 0); }
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($opt = '', $typ = 1)
    {
        $this->db->select('geopos_guides.id');
        $this->db->from($this->table);
        $this->db->where('geopos_guides.i_class', 0);
		$this->db->where('geopos_guides.typeguide', $typ);
        if ($opt) {
            $this->db->where('geopos_guides.eid', $opt);

        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_guides.loc', $this->aauth->get_user()->loc);
        }  elseif(!BDATA) { $this->db->where('geopos_guides.loc', 0); }
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