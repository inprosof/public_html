<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Assetcategory_model extends CI_Model

{



    public function category_list($type = 0, $rel = 0)
    {
        $query = $this->db->query("SELECT id,title,type FROM geopos_assets_cat");
        return $query->result_array();
    }

	public function category_type_list($type = 0, $rel = 0)
    {
        $query = $this->db->query("SELECT id,name FROM geopos_assets_cat_type");
        return $query->result_array();
    }

    public function warehouse_list()

    {

        $where = '';





        if (!BDATA) $where = "WHERE  (loc=0) ";

        if ($this->aauth->get_user()->loc) {

            $where = "WHERE  (loc=" . $this->aauth->get_user()->loc . " ) ";

            if (BDATA) $where = "WHERE  (loc=" . $this->aauth->get_user()->loc . " OR geopos_warehouse.loc=0) ";

        }





        $query = $this->db->query("SELECT id,title

FROM geopos_warehouse $where 



ORDER BY id DESC");

        return $query->result_array();

    }



    public function category_stock()

    {

           $query = $this->db->query("SELECT id,title FROM geopos_assets_cat");
		   
        //$query = $this->db->query("SELECT c.*,p.pc,p.salessum,p.worthsum,p.qty FROM geopos_product_cat AS c LEFT JOIN ( SELECT geopos_products.pcat,COUNT(geopos_products.pid) AS pc,SUM(geopos_products.product_price*geopos_products.qty) AS salessum, SUM(geopos_products.fproduct_price*geopos_products.qty) AS worthsum,SUM(geopos_products.qty) AS qty FROM geopos_products LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id  $whr GROUP BY geopos_products.pcat ) AS p ON c.id=p.pcat WHERE c.c_type=0");

        return $query->result_array();

    }



    public function category_sub_stock($id = 0)

    {

        $whr = '';

        if (!BDATA) $whr = "WHERE  (geopos_warehouse.loc=0) ";

        if ($this->aauth->get_user()->loc) {

            $whr = "WHERE  (geopos_warehouse.loc=" . $this->aauth->get_user()->loc . " ) ";

            if (BDATA) $whr = "WHERE  (geopos_warehouse.loc=" . $this->aauth->get_user()->loc . " OR geopos_warehouse.loc=0) ";

        }



        $whr2 = '';



        $query = $this->db->query("SELECT c.*,p.pc,p.salessum,p.worthsum,p.qty,p.sub_id FROM geopos_product_cat AS c LEFT JOIN ( SELECT geopos_products.sub_id,COUNT(geopos_products.pid) AS pc,SUM(geopos_products.product_price*geopos_products.qty) AS salessum, SUM(geopos_products.fproduct_price*geopos_products.qty) AS worthsum,SUM(geopos_products.qty) AS qty FROM geopos_products LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id  $whr GROUP BY geopos_products.sub_id ) AS p ON c.id=p.sub_id WHERE c.c_type=1 AND c.rel_id='$id'");

        return $query->result_array();

    }



    public function warehouse()

    {

        $where = '';

        if ($this->aauth->get_user()->loc) {

            $where = ' WHERE c.loc=' . $this->aauth->get_user()->loc;



            if (BDATA) $where = ' WHERE c.loc=' . $this->aauth->get_user()->loc . ' OR c.loc=0';

        } elseif (!BDATA) {

            $where = ' WHERE  c.loc=0';

        }

        $query = $this->db->query("SELECT c.*,p.pc,p.salessum,p.worthsum,p.qty FROM geopos_warehouse AS c LEFT JOIN ( SELECT warehouse,COUNT(pid) AS pc,SUM(product_price*qty) AS salessum, SUM(fproduct_price*qty) AS worthsum,SUM(qty) AS qty FROM  geopos_products GROUP BY warehouse ) AS p ON c.id=p.warehouse  $where");

        return $query->result_array();

    }



    public function cat_ware($id, $loc = 0)

    {

        $qj = '';

        if ($loc) $qj = "AND w.loc='$loc'";

        $query = $this->db->query("SELECT c.id AS cid, w.id AS wid,c.title AS catt,w.title AS watt FROM geopos_products AS p LEFT JOIN geopos_product_cat AS c ON p.pcat=c.id LEFT JOIN geopos_warehouse AS w ON p.warehouse=w.id WHERE p.pid='$id' $qj ");

        return $query->row_array();
    }





    public function addnew($cat_name,$cat_ty_name)
    {
        $data = array(
            'title' => $cat_name,
			'type' => $cat_ty_name,
        );
		
		$url = "<a href='" . base_url('Assetcategory/add') . "' class='btn btn-blue btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a> <a href='" . base_url('Assetcategory') . "' class='btn btn-grey-blue btn-lg'><span class='fa fa-list-alt' aria-hidden='true'></span>  </a>";
        if ($this->db->insert('geopos_assets_cat', $data)) {
            //$this->aauth->applog("[Category Created] $cat_name ID " . $this->db->insert_id(), $this->aauth->get_user()->username);
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . " $url"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }



    public function addwarehouse($cat_name, $cat_desc, $lid)

    {

        $data = array(

            'title' => $cat_name,

            'extra' => $cat_desc,

            'loc' => $lid

        );



        if ($this->db->insert('geopos_warehouse', $data)) {

            $this->aauth->applog("[WareHouse Created] $cat_name ID " . $this->db->insert_id(), $this->aauth->get_user()->username);

               $url = "<a href='" . base_url('productcategory/addwarehouse') . "' class='btn btn-blue btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a> <a href='" . base_url('productcategory/warehouse') . "' class='btn btn-grey-blue btn-lg'><span class='fa fa-list-alt' aria-hidden='true'></span>  </a>";

            echo json_encode(array('status' => 'Success', 'message' =>

                $this->lang->line('ADDED') . $url));

        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

                $this->lang->line('ERROR')));

        }



    }



    public function edit($catid, $product_cat_name, $asset_cat_type)
    {
        $data = array(
            'title' => $product_cat_name,
			'type' => $asset_cat_type,
        );

        $this->db->set($data);
        $this->db->where('id', $catid);
        if ($this->db->update('geopos_assets_cat')) {
            //$this->aauth->applog("[Category Edited] $product_cat_name ID " . $catid, $this->aauth->get_user()->username);
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }



    public function editwarehouse($catid, $product_cat_name, $product_cat_desc, $lid)
    {

        $data = array(

            'title' => $product_cat_name,

            'extra' => $product_cat_desc,

            'loc' => $lid

        );





        $this->db->set($data);

        $this->db->where('id', $catid);



        if ($this->db->update('geopos_warehouse')) {

            $this->aauth->applog("[Warehouse Edited] $product_cat_name ID " . $catid, $this->aauth->get_user()->username);

            echo json_encode(array('status' => 'Success', 'message' =>

                $this->lang->line('UPDATED')));

        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

                $this->lang->line('ERROR')));

        }



    }



    public function sub_cat($id = 0)

    {

        $this->db->select('*');

        $this->db->from('geopos_product_cat');

        $this->db->where('rel_id', $id);

        $this->db->where('c_type', 1);

        $this->db->limit(1);

        $query = $this->db->get();

        return $query->row_array();

    }



       public function sub_cat_curr($id = 0)

    {

        $this->db->select('*');

        $this->db->from('geopos_product_cat');

        $this->db->where('id', $id);

        $this->db->where('c_type', 1);

        $this->db->limit(1);

        $query = $this->db->get();

        return $query->row_array();

    }



    public function sub_cat_list($id = 0)

    {

        $this->db->select('*');

        $this->db->from('geopos_product_cat');

        $this->db->where('rel_id', $id);

        $this->db->where('c_type', 1);

        $query = $this->db->get();

        return $query->result_array();

    }





}