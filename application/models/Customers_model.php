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

class Customers_model extends CI_Model
{

    var $table = 'geopos_customers';
    var $column_order = array(null, 'geopos_customers.name', 'geopos_customers.address', 'geopos_customers.email', 'geopos_customers.phone', null);
    var $column_search = array('geopos_customers.name', 'geopos_customers.phone', 'geopos_customers.address', 'geopos_customers.city', 'geopos_customers.email', 'geopos_customers.docid');
	var $trans_column_order = array(null,'geopos_transactions.date', 'geopos_transactions.debit', 'geopos_transactions.credit', 'geopos_transactions.account', 'geopos_config.val1 as methodname', 'geopos_trans_cat.cod as cod_cat');
    var $trans_column_search = array('geopos_transactions.date', 'geopos_transactions.debit', 'geopos_transactions.credit', 'geopos_transactions.account', 'geopos_config.val1 as methodname','geopos_trans_cat.cod as cod_cat');
    var $inv_column_order = array('geopos_series.serie AS serie_name','tid', 'geopos_invoices.invoicedate', null,null,null, 'geopos_invoices.status',null,null);
    var $inv_column_search = array('geopos_series.serie AS serie_name','tid', 'geopos_invoices.invoicedate', null,null,null, 'geopos_invoices.status');
	 
    var $order = array('id' => 'DESC');
    var $inv_order = array('geopos_invoices.tid' => 'desc');
    var $qto_order = array('geopos_quotes.tid' => 'desc');
	var $notecolumn_order = array(null, 'geopos_notes.cdate', 'geopos_notes.last_edit', 'geopos_notes.title', 'name_add');
    var $notecolumn_search = array('geopos_notes.id', 'geopos_notes.cdate', 'geopos_notes.last_edit', 'geopos_notes.title', 'name_add');
	
    var $pcolumn_order = array('geopos_projects.status', 'geopos_projects.name', 'geopos_projects.edate', 'geopos_projects.worth', null);
    var $pcolumn_search = array('geopos_projects.name', 'geopos_projects.edate', 'geopos_projects.status');
    var $ptcolumn_order = array('status', 'name', 'duedate', 'start', null, null);
    var $ptcolumn_search = array('name', 'edate', 'status');
    var $porder = array('id' => 'DESC');


    private function _get_datatables_query($id = '')
    {
        $due = $this->input->post('due');
		$inac = $this->input->post('inac');
        if ($due) {
            $this->db->select('geopos_customers.*,SUM(geopos_invoices.total) AS total,SUM(geopos_invoices.pamnt) AS pamnt');
            $this->db->from('geopos_invoices');
            $this->db->where('geopos_invoices.status!=', 'paid');
            $this->db->join('geopos_customers', 'geopos_customers.id = geopos_invoices.csd', 'left');
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_customers.loc', 0);
            }
            if ($id != '') {
                $this->db->where('geopos_customers.gid', $id);
            }
            $this->db->group_by('geopos_invoices.csd');
            $this->db->order_by('total', 'desc');

        }else {
			$this->db->from($this->table);
			if($inac){
				$this->db->where('inactive', 1);
			}else{
				$this->db->where('inactive', 0);
			}
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            if ($id != '') {
                $this->db->where('gid', $id);
            }

        }
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            $search = $this->input->post('search');
			if($search != null){
				$value = $search['value'];
				if ($value) // if datatable send POST for search
				{

					if ($i === 0) // first loop
					{
						$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
						$this->db->like($item, $value);
					} else {
						$this->db->or_like($item, $value);
					}

					if (count($this->column_search) - 1 == $i) //last loop
						$this->db->group_end(); //close bracket
				}
				$i++;
			}
            
        }
        $search = $this->input->post('order');
        if ($search) // here order processing
        {
            $this->db->order_by($this->column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($id = '')
    {
        $this->_get_datatables_query($id);
        if ($this->aauth->get_user()->loc) {
           // $this->db->where('loc', $this->aauth->get_user()->loc);
        }
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($id = '')
    {
        $this->_get_datatables_query($id);
        $query = $this->db->get();
        if ($id != '') {
            $this->db->where('geopos_customers.gid', $id);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
        }
        return $query->num_rows($id = '');
    }

    public function count_all($id = '')
    {
        $this->_get_datatables_query($id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
        }
        if ($id != '') {
            $this->db->where('geopos_customers.gid', $id);
        }
        $query = $this->db->get();
        return $query->num_rows($id = '');
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
	
	public function details($custid,$loc=true)
    {
        $this->db->select('geopos_customers.*, b.lang, l1.name as namecountry, l2.name as namecountry_s, geopos_cust_group.title as gr_client');
        $this->db->from($this->table);
		$this->db->join('users as b', 'b.cid = geopos_customers.id', 'left');
		$this->db->join('geopos_countrys as l1', 'geopos_customers.country = l1.prefix', 'left');
		$this->db->join('geopos_countrys as l2', 'geopos_customers.country_s = l2.prefix', 'left');
		$this->db->join('geopos_cust_group', 'geopos_customers.gid = geopos_cust_group.id', 'left');
        $this->db->where('geopos_customers.id', $custid);
        if($loc) {
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_customers.loc', 0);
            }
        }
        $query = $this->db->get();
        return $query->row_array();
    }
	
	public function detailsFromProject($custid)
    {
        $this->db->select('geopos_customers.*');
        $this->db->from($this->table);
		$this->db->join('geopos_projects', 'geopos_projects.cid = geopos_customers.id', 'left');
        $this->db->where('geopos_projects.id', $custid);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function money_details($custid)
    {
        $this->db->select('SUM(debit) AS debit,SUM(credit) AS credit, geopos_config.val1 as methodname, geopos_trans_cat.cod as cod_cat, geopos_trans_cat.name as name_cat');
        $this->db->from('geopos_transactions');
		$this->db->join('geopos_config', 'geopos_transactions.method = geopos_config.id', 'left');
		$this->db->join('geopos_trans_cat', 'geopos_transactions.cat = geopos_trans_cat.id', 'left');
        $this->db->where('payerid', $custid);
        $this->db->where('ext', 0);
        $query = $this->db->get();
        return $query->row_array();
    }
	
	
	public function verifytax($taxid)
	{
		$this->db->select('*');
        $this->db->from('geopos_customers');
        $this->db->where('taxid', $taxid);
        $query = $this->db->get();
        return $query->num_rows();
	}

    public function due_details($custid)
    {

        $this->db->select('SUM(total) AS total,SUM(pamnt) AS pamnt,SUM(discount) AS discount,');
        $this->db->from('geopos_invoices');
        $this->db->where('csd', $custid);
        $query = $this->db->get();
        return $query->row_array();
    }


    public function add($name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s, $language = '', $create_login = true, $password = '', $docid = '', $discount = 0)
    {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  echo json_encode(array('status' => 'Validação de Email', 'message' => 'O email inserido não é válido! Por favor verifique.'));
		}else{
			$this->db->select('email');
			$this->db->from('geopos_customers');
			$this->db->where('email', $email);
			$query = $this->db->get();
			$valid = $query->row_array();
			$lecture = $valid['email'] ?? null;
			if (!$lecture) {
				if (!$discount) {
					$this->db->select('disc_rate');
					$this->db->from('geopos_cust_group');
					$this->db->where('id', $customergroup);
					$query = $this->db->get();
					$result = $query->row_array();
					$discount = $result['disc_rate'];
				}

				$data = array(
					'name' => $name,
					'company' => $company,
					'phone' => $phone,
					'email' => $email,
					'address' => $address,
					'city' => $city,
					'region' => $region,
					'country' => $country,
					'postbox' => $postbox,
					'gid' => $customergroup,
					'taxid' => $taxid,
					'name_s' => $name_s,
					'phone_s' => $phone_s,
					'email_s' => $email_s,
					'address_s' => $address_s,
					'city_s' => $city_s,
					'region_s' => $region_s,
					'country_s' => $country_s,
					'postbox_s' => $postbox_s,
					'docid' => $docid,
					'inactive' => 0,
					'discount_c' => $discount
				);


				if ($this->aauth->get_user()->loc) {
					$data['loc'] = $this->aauth->get_user()->loc;
				}

				if ($this->db->insert('geopos_customers', $data)) {
					$cid = $this->db->insert_id();
					$p_string = '';
					$temp_password = '';
					if ($create_login) {

						if ($password) {
							$temp_password = $password;
						} else {
							$temp_password = rand(200000, 999999);
						}

						$pass = password_hash($temp_password, PASSWORD_DEFAULT);
						$data = array(
							'user_id' => 1,
							'status' => 'active',
							'is_deleted' => 0,
							'name' => $name,
							'password' => $pass,
							'email' => $email,
							'user_type' => 'Member',
							'cid' => $cid,
							'lang' => $language
						);

						$this->db->insert('users', $data);
						$p_string = ' A senha temporária é: ' . $temp_password . ' ';
					}
					$this->aauth->applog("[Client Added] $name ID " . $cid, $this->aauth->get_user()->username);
					echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . $p_string . '&nbsp;<a href="' . base_url('customers/view?id=' . $cid) . '" class="btn btn-info btn-sm"><span class="icon-eye"></span>' . $this->lang->line('View') . '</a>', 'cid' => $cid, 'pass' => $temp_password, 'discount' => amountFormat_general($discount)));

					$this->custom->save_fields_data($cid, 1);

					$this->db->select('other');
					$this->db->from('univarsal_api');
					$this->db->where('id', 64);
					$query = $this->db->get();
					$othe = $query->row_array();

					if ($othe['other']) {
						$auto_mail = $this->send_mail_auto($email, $name, $temp_password);
						$this->load->model('communication_model');
						$attachmenttrue = false;
						$attachment = '';
						$this->communication_model->send_corn_email($email, $name, $auto_mail['subject'], $auto_mail['message'], $attachmenttrue, $attachment, $this->aauth->get_user()->loc);
					}

				} else {
					echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
				}
			} else if (!$valid['email']) {
				if (!$discount) {
					$this->db->select('disc_rate');
					$this->db->from('geopos_cust_group');
					$this->db->where('id', $customergroup);
					$query = $this->db->get();
					$result = $query->row_array();
					$discount = $result['disc_rate'];
				}


				$data = array(
					'name' => $name,
					'company' => $company,
					'phone' => $phone,
					'email' => $email,
					'address' => $address,
					'city' => $city,
					'region' => $region,
					'country' => $country,
					'postbox' => $postbox,
					'gid' => $customergroup,
					'taxid' => $taxid,
					'name_s' => $name_s,
					'phone_s' => $phone_s,
					'email_s' => $email_s,
					'address_s' => $address_s,
					'city_s' => $city_s,
					'region_s' => $region_s,
					'country_s' => $country_s,
					'postbox_s' => $postbox_s,
					'docid' => $docid,
					'inactive' => 0,
					'discount_c' => $discount
				);


				if ($this->aauth->get_user()->loc) {
					$data['loc'] = $this->aauth->get_user()->loc;
				}

				if ($this->db->insert('geopos_customers', $data)) {
					$cid = $this->db->insert_id();
					$p_string = '';
					$temp_password = '';
					if ($create_login) {
						if ($password) {
							$temp_password = $password;
						} else {
							$temp_password = rand(200000, 999999);
						}

						$pass = password_hash($temp_password, PASSWORD_DEFAULT);
						$data = array(
							'user_id' => 1,
							'status' => 'active',
							'is_deleted' => 0,
							'name' => $name,
							'password' => $pass,
							'email' => $email,
							'user_type' => 'Member',
							'cid' => $cid,
							'lang' => $language
						);

						$this->db->insert('users', $data);
						$p_string = ' A senha temporária é: ' . $temp_password . ' ';
					}
					$this->aauth->applog("[Client Added] $name ID " . $cid, $this->aauth->get_user()->username);
					echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . $p_string . '&nbsp;<a href="' . base_url('customers/view?id=' . $cid) . '" class="btn btn-info btn-sm"><span class="icon-eye"></span>' . $this->lang->line('View') . '</a>', 'cid' => $cid, 'pass' => $temp_password, 'discount' => amountFormat_general($discount)));

					$this->custom->save_fields_data($cid, 1);

					$this->db->select('other');
					$this->db->from('univarsal_api');
					$this->db->where('id', 64);
					$query = $this->db->get();
					$othe = $query->row_array();

					if ($othe['other']) {
						$auto_mail = $this->send_mail_auto($email, $name, $temp_password);
						$this->load->model('communication_model');
						$attachmenttrue = false;
						$attachment = '';
						$this->communication_model->send_corn_email($email, $name, $auto_mail['subject'], $auto_mail['message'], $attachmenttrue, $attachment, $this->aauth->get_user()->loc);
					}

				} else {
					echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
				}
			}else {
				echo json_encode(array('status' => 'Erro de duplicação', 'message' => 'Email já registado por favor insira um Novo. Por favor verifique!'));
			}
		}
    }


    public function edit($id, $name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s, $docid = '', $inactive, $language = '', $discount = 0)
    {
        $data = array(
            'name' => $name,
            'company' => $company,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'postbox' => $postbox,
            'gid' => $customergroup,
            'taxid' => $taxid,
            'name_s' => $name_s,
            'phone_s' => $phone_s,
            'email_s' => $email_s,
            'address_s' => $address_s,
            'city_s' => $city_s,
            'region_s' => $region_s,
            'country_s' => $country_s,
            'postbox_s' => $postbox_s,
            'docid' => $docid,
            'inactive' => $inactive,
            'discount_c' => $discount
        );


        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        if ($this->db->update('geopos_customers')) {
			$this->custom->edit_save_fields_data($id, 1);
            $data = array(
                'name' => $name,
                'email' => $email,
                'lang' => $language
            );
            $this->db->set($data);
            $this->db->where('cid', $id);
            $this->db->update('users');
            $this->aauth->applog("[Client Updated] $name ID " . $id, $this->aauth->get_user()->username);
            echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
        }

    }

    public function changepassword($id, $password)
    {
        $pass = password_hash($password, PASSWORD_DEFAULT);
        $data = array(
            'password' => $pass
        );


        $this->db->set($data);
        $this->db->where('cid', $id);

        if ($this->db->update('users')) {
            echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
        }

    }

    public function editpicture($id, $pic)
    {
        $this->db->select('picture');
        $this->db->from($this->table);
        $this->db->where('id', $id);

        $query = $this->db->get();
        $result = $query->row_array();


        $data = array(
            'picture' => $pic
        );


        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        if ($this->db->update('geopos_customers') AND $result['picture'] != 'example.png') {

            unlink(FCPATH . 'userfiles/customers/' . $result['picture']);
            unlink(FCPATH . 'userfiles/customers/thumbnail/' . $result['picture']);
        }


    }

    public function group_list()
    {
        $whr = "";
        if ($this->aauth->get_user()->loc) {
            $whr = "WHERE (geopos_customers.loc=" . $this->aauth->get_user()->loc . " ) ";
            if (BDATA) $whr = "WHERE (geopos_customers.loc=" . $this->aauth->get_user()->loc . " OR geopos_customers.loc=0 ) ";
        } elseif (!BDATA) {
            $whr = "WHERE  geopos_customers.loc=0  ";
        }

        $query = $this->db->query("SELECT c.*,p.pc FROM geopos_cust_group AS c LEFT JOIN ( SELECT gid,COUNT(gid) AS pc FROM geopos_customers $whr GROUP BY gid) AS p ON p.gid=c.id");
        return $query->result_array();
    }
	
	
	public function verifydelete($id)
	{
		$return = 0;
		$this->db->select('*');
		$this->db->from('geopos_invoices');
		$this->db->where('id', $id);
		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			$return = 1;
		}else{
			$this->db->select('*');
			$this->db->from('geopos_quotes');
			$this->db->where('id', $id);
			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				$return = 2;
			}else{
				$this->db->select('*');
				$this->db->from('geopos_receipts');
				$this->db->where('id', $id);
				$query = $this->db->get();
				if($query->num_rows() > 0)
				{
					$return = 3;
				}else{
					$this->db->select('*');
					$this->db->from('geopos_transactions');
					$this->db->where('id', $id);
					$query = $this->db->get();
					if($query->num_rows() > 0)
					{
						$return = 4;
					}else{
						$this->db->select('*');
						$this->db->from('geopos_projects');
						$this->db->where('id', $id);
						$query = $this->db->get();
						if($query->num_rows() > 0)
						{
							$return = 5;
						}else{
							$this->db->select('*');
							$this->db->from('geopos_customers_notes');
							$this->db->where('id', $id);
							$query = $this->db->get();
							if($query->num_rows() > 0)
							{
								$return = 6;
							}else{
								$this->db->select('*');
								$this->db->from('geopos_docs_intern');
								$this->db->where('id', $id);
								$query = $this->db->get();
								if($query->num_rows() > 0)
								{
									$return = 7;
								}else{
									$this->db->select('*');
									$this->db->from('geopos_draft');
									$this->db->where('id', $id);
									$query = $this->db->get();
									if($query->num_rows() > 0)
									{
										$return = 8;
									}else{
										$this->db->select('*');
										$this->db->from('geopos_guides');
										$this->db->where('id', $id);
										$query = $this->db->get();
										if($query->num_rows() > 0)
										{
											$return = 9;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $return;
	}

    public function delete($id)
    {
        if ($this->aauth->get_user()->loc) {
            $this->db->delete('geopos_customers', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));

        } elseif (!BDATA) {
            $this->db->delete('geopos_customers', array('id' => $id, 'loc' => 0));
        } else {
            $this->db->delete('geopos_customers', array('id' => $id));
        }

        if ($this->db->affected_rows()) {
            $this->aauth->applog("[Client Deleted]  ID " . $id, $this->aauth->get_user()->username);
            $this->db->delete('users', array('cid' => $id));
            $this->custom->del_fields($id, 1);
            $this->db->delete('geopos_notes', array('fid' => $id, 'rid' => 1));
            //docs
            $this->db->select('filename');
            $this->db->from('geopos_documents');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $result = $query->row_array();
            if ($this->db->delete('geopos_documents', array('fid' => $id, 'rid' => 1))) {
                @unlink(FCPATH . 'userfiles/documents/' . $result['filename']);
                $this->aauth->applog("[Client Doc Deleted]  DocId $id CID " . $id, $this->aauth->get_user()->username);
                //docs

            }
            return true;
        }

    }


    //transtables

    function trans_table($id)
    {
        $this->_get_trans_table_query($id);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
	
	private function _get_trans_table_query($id)
    {
		$this->db->select('geopos_transactions.id,geopos_transactions.date,geopos_transactions.debit, geopos_transactions.credit, geopos_transactions.account, geopos_config.val1 as methodname, geopos_trans_cat.cod as cod_cat, geopos_trans_cat.name as name_cat');
        $this->db->from('geopos_transactions');
		$this->db->join('geopos_config', 'geopos_transactions.method = geopos_config.id', 'left');
		$this->db->join('geopos_trans_cat', 'geopos_transactions.cat = geopos_trans_cat.id', 'left');
        $this->db->where('geopos_transactions.payerid', $id);
        $this->db->where('geopos_transactions.ext', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $i = 0;
        foreach ($this->trans_column_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->trans_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) // here order processing
        {
            $this->db->order_by($this->trans_column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function trans_count_filtered($id = '')
    {
        $this->_get_trans_table_query($id);
        $query = $this->db->get();
        if ($id != '') {
            $this->db->where('payerid', $id);
        }
        return $query->num_rows($id = '');
    }

    public function trans_count_all($id = '')
    {
        $this->_get_trans_table_query($id);
        $query = $this->db->get();
        if ($id != '') {
            $this->db->where('payerid', $id);
        }
    }

    private function _inv_datatables_query($id, $tyd = 0)
    {
        $this->db->select('geopos_invoices.id,geopos_series.serie AS serie_name,geopos_invoices.tid,geopos_invoices.invoicedate, geopos_customers.name, geopos_customers.taxid, geopos_invoices.subtotal, geopos_invoices.tax, geopos_invoices.total, geopos_invoices.status, geopos_invoices.pamnt, geopos_invoices.invoiceduedate, geopos_invoices.i_class,geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.csd', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        if ($tyd) 
			$this->db->where('geopos_invoices.i_class >', 1);
		$this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_invoices.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_invoices.serie', 'left');
        $i = 0;

        foreach ($this->inv_column_search as $item) // loop column
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

                if (count($this->inv_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->inv_column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->inv_order)) {
            $order = $this->inv_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function inv_datatables($id, $tyd = 0)
    {
        $this->_inv_datatables_query($id, $tyd);

        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function inv_count_filtered($id)
    {
        $this->_inv_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function inv_count_all($id)
    {
        $this->db->from('geopos_invoices');
        $this->db->where('csd', $id);
        return $this->db->count_all_results();
    }


    private function _qto_datatables_query($id, $tyd = 0)
    {
        $this->db->select('geopos_quotes.*,geopos_irs_typ_doc.type AS irs_type_s, geopos_irs_typ_doc.description AS irs_type_n,geopos_series.serie AS serie_name');
        $this->db->from('geopos_quotes');
        $this->db->where('geopos_quotes.csd', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_quotes.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_quotes.loc', 0);
        }
		$this->db->join('geopos_customers', 'geopos_quotes.csd=geopos_customers.id', 'left');
		$this->db->join('geopos_irs_typ_doc', 'geopos_quotes.irs_type = geopos_irs_typ_doc.id', 'left');
		$this->db->join('geopos_series', 'geopos_series.id = geopos_quotes.serie', 'left');
        $i = 0;

        foreach ($this->inv_column_search as $item) // loop column
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

                if (count($this->inv_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->qto_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->qto_order)) {
            $order = $this->qto_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function qto_datatables($id, $tyd = 0)
    {
        $this->_qto_datatables_query($id);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function qto_count_filtered($id)
    {
        $this->_qto_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function qto_count_all($id)
    {
        $this->db->from('geopos_quotes');
        $this->db->where('csd', $id);
        return $this->db->count_all_results();
    }

    public function group_info($id)
    {

        $this->db->from('geopos_cust_group');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function activity($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_metadata');
        $this->db->where('type', 21);
        $this->db->where('rid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function recharge($id, $amount)
    {

        $this->db->set('balance', "balance+$amount", FALSE);
        $this->db->where('id', $id);

        $this->db->update('geopos_customers');

        $data = array(
            'type' => 21,
            'rid' => $id,
            'col1' => $amount,
            'col2' => date('Y-m-d H:i:s') . ' Account Recharge by ' . $this->aauth->get_user()->username
        );


        if ($this->db->insert('geopos_metadata', $data)) {
            $this->aauth->applog("[Client Wallet Recharge] Amt-$amount ID " . $id, $this->aauth->get_user()->username);
            return true;
        } else {
            return false;
        }

    }

    private function _project_datatables_query($cday = '')
    {
        $this->db->select("geopos_projects.*,geopos_customers.name AS customer");
        $this->db->from('geopos_projects');
        $this->db->join('geopos_customers', 'geopos_projects.cid = geopos_customers.id', 'left');


        $this->db->where('geopos_projects.cid=', $cday);


        $i = 0;

        foreach ($this->pcolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->pcolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->porder)) {
            $order = $this->porder;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function project_datatables($cday = '')
    {


        $this->_project_datatables_query($cday);

        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function project_count_filtered($cday = '')
    {
        $this->_project_datatables_query($cday);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function project_count_all($cday = '')
    {
        $this->_project_datatables_query($cday);
        $query = $this->db->get();
        return $query->num_rows();
    }

    //notes

    private function _notes_datatables_query($id)
    {
		$this->db->select("geopos_notes.id, geopos_notes.cdate, geopos_notes.last_edit, geopos_notes.title, geopos_employees.name as name_add");
        $this->db->from('geopos_notes');
		$this->db->join('geopos_users', 'geopos_users.id = geopos_notes.cid', 'left');
		$this->db->join('geopos_employees', 'geopos_employees.username = geopos_users.username', 'left');
        $this->db->where('fid', $id);
        $this->db->where('rid', 1);
        $i = 0;

        foreach ($this->notecolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->notecolumn_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function notes_datatables($id)
    {
        $this->_notes_datatables_query($id);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function notes_count_filtered($id)
    {
        $this->_notes_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function notes_count_all($id)
    {
        $this->_notes_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function editnote($id, $title, $content, $cid)
    {

        $data = array('title' => $title, 'content' => $content, 'last_edit' => date('Y-m-d H:i:s'));
        $this->db->set($data);
        $this->db->where('id', $id);
        $this->db->where('fid', $cid);
        if ($this->db->update('geopos_notes')) {
            $this->aauth->applog("[Client Note Edited]  NoteId $id CID " . $cid, $this->aauth->get_user()->username);
            return true;
        } else {
            return false;
        }

    }

    public function note_v($id, $cid)
    {
        $this->db->select('*');
        $this->db->from('geopos_notes');
        $this->db->where('id', $id);
        $this->db->where('fid', $cid);
        $query = $this->db->get();
        return $query->row_array();
    }

    function addnote($title, $content, $cid)
    {
        $this->aauth->applog("[Client Note Added]  NoteId $title CID " . $cid, $this->aauth->get_user()->username);
        $data = array('title' => $title, 'content' => $content, 'cdate' => date('Y-m-d'), 'last_edit' => date('Y-m-d H:i:s'), 'cid' => $this->aauth->get_user()->id, 'fid' => $cid, 'rid' => 1);
        return $this->db->insert('geopos_notes', $data);

    }

    function deletenote($id, $cid)
    {
        $this->aauth->applog("[Client Note Deleted]  NoteId $id CID " . $cid, $this->aauth->get_user()->username);
        return $this->db->delete('geopos_notes', array('id' => $id, 'fid' => $cid, 'rid' => 1));

    }

    //documents list
	var $doccolumn_order = array(null, 'geopos_documents.cdate', 'geopos_documents.title', 'name_add','geopos_documents.filename');
    var $doccolumn_search = array('geopos_documents.cdate', 'geopos_documents.title', 'name_add');

    public function documentlist($cid)
    {
        $this->db->select('*');
        $this->db->from('geopos_documents');
        $this->db->where('fid', $cid);
        $this->db->where('rid', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    function adddocument($title, $filename, $cid)
    {
        $this->aauth->applog("[Client Doc Added]  DocId $title CID " . $cid, $this->aauth->get_user()->username);
        $data = array('title' => $title, 'filename' => $filename, 'cdate' => date('Y-m-d'), 'cid' => $this->aauth->get_user()->id, 'fid' => $cid, 'rid' => 1);
        return $this->db->insert('geopos_documents', $data);

    }

    function deletedocument($id, $cid)
    {
        $this->db->select('filename');
        $this->db->from('geopos_documents');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $this->db->trans_start();
        if ($this->db->delete('geopos_documents', array('id' => $id, 'fid' => $cid, 'rid' => 1))) {
            if (@unlink(FCPATH . 'userfiles/documents/' . $result['filename'])) {
                $this->aauth->applog("[Client Doc Deleted]  DocId $id CID " . $cid, $this->aauth->get_user()->username);
                $this->db->trans_complete();
                return true;
            } else {
                $this->db->trans_rollback();
                return false;
            }

        } else {
            return false;
        }
    }


    function document_datatables($cid)
    {
        $this->document_datatables_query($cid);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    private function document_datatables_query($cid)
    {	
		$this->db->select("geopos_documents.id, geopos_documents.cdate, geopos_documents.title, geopos_employees.name as name_add,geopos_documents.filename");
        $this->db->from('geopos_documents');
		$this->db->join('geopos_users', 'geopos_users.id = geopos_documents.cid', 'left');
		$this->db->join('geopos_employees', 'geopos_employees.username = geopos_users.username', 'left');
        $this->db->where('fid', $cid);
        $this->db->where('rid', 1);
        $i = 0;

        foreach ($this->doccolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->doccolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->doccolumn_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function document_count_filtered($cid)
    {
        $this->document_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function document_count_all($cid)
    {
        $this->document_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function send_mail_auto($email, $name, $password)
    {
        $this->load->library('parser');
        $this->load->model('templates_model', 'templates');
        $template = $this->templates->template_info(16);
		
		$mailfromtilte = '';
		$mailfrom = '';
		
		$this->db->select("emailo_remet, email_app");
		$this->db->from('geopos_system_permiss');
		if($this->aauth->get_user()->loc > 0){
			$this->db->where('loc', $this->aauth->get_user()->loc);
		}else{
			$this->db->where('loc', 0);
		}
		$query = $this->db->get();
		$vals = $query->row_array();
		$mailfromtilte = $vals['emailo_remet'];
		if($mailfromtilte == '')
		{
			$mailfromtilte = $this->config->item('ctitle');
		}
		$mailfrom = $vals['email_app'];		
        $data = array(
            'Company' => $mailfromtilte,
            'NAME' => $name
        );
        $subject = $this->parser->parse_string($template['key1'], $data, TRUE);
        $data = array(
            'Company' => $mailfromtilte,
            'NAME' => $name,
            'EMAIL' => $email,
            'URL' => base_url() . 'crm',
            'PASSWORD' => $password,
            'CompanyDetails' => '<h6><strong>' . $mailfromtilte . ',</strong></h6>
			<address>' . $this->config->item('address') . '<br>' . $this->config->item('address2') . '</address>
             ' . $this->lang->line('Phone') . ' : ' . $this->config->item('phone') . '<br>  ' . $this->lang->line('Email') . ' : ' . $this->config->item('email'),
        );
        $message = $this->parser->parse_string($template['other'], $data, TRUE);
        return array('subject' => $subject, 'message' => $message);
    }
	
	/**
	 * VIES VAT number validation
	 *
	 * @author Eugen Mihailescu
	 *        
	 * @param string $countryCode           
	 * @param string $vatNumber         
	 * @param int $timeout          
	 */
	function array_key_replace($item, $replace_with, array $array){
		$updated_array = [];
		foreach ($array as $key => $value) {
			if (!is_array($value) && $key == $item) {
				$updated_array = array_merge($updated_array, [$replace_with => $value]);
				continue;
			}
			$updated_array = array_merge($updated_array, [$key => $value]);
		}
		return $updated_array;
	}

	 function string_between_two_string($str, $starting_word, $ending_word)
	{
		$subtring_start = strpos($str, $starting_word);
		//Adding the starting index of the starting word to
		//its length would give its ending index
		$subtring_start += strlen($starting_word); 
		//Length of our required sub string
		$size = strpos($str, $ending_word) - $subtring_start; 
		// Return the substring from the index substring_start of length size
		return substr($str, $subtring_start, $size); 
	}

	public function ValidaVIES($inif, $icontry, $timeout = 30)
    {
		//'VIES_URL', 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService'
		$response = array ();
		$pattern = '/<(%s).*?>([\s\S]*)<\/\1/';
		$keys = array (
				'countryCode',
				'vatNumber',
				'requestDate',
				'valid',
				'name',
				'address' 
		);
		//Changed envelope
		 $content = "<soap:Envelope xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'
			 xmlns:tns1='urn:ec.europa.eu:taxud:vies:services:checkVat:types'
			 xmlns:impl='urn:ec.europa.eu:taxud:vies:services:checkVat'>
			 <soap:Header>
			 </soap:Header>
			 <soap:Body>
				<tns1:checkVat xmlns:tns1='urn:ec.europa.eu:taxud:vies:services:checkVat:types'
				xmlns='urn:ec.europa.eu:taxud:vies:services:checkVat:types'>
				<tns1:countryCode>".$icontry."</tns1:countryCode>
				<tns1:vatNumber>".$inif."</tns1:vatNumber>
			   </tns1:checkVat>
			 </soap:Body>
			</soap:Envelope>";

		$opts = array (
				'http' => array (
						'method' => 'POST',
						'header' => "Content-type: text/xml",
						'content' => sprintf ( $content ),
						'timeout' => $timeout 
				) 
		);

		$ctx = stream_context_create ( $opts );
		$result = file_get_contents ( 'https://ec.europa.eu/taxation_customs/vies/services/checkVatService', false, $ctx );
		
		$valid = $this->string_between_two_string($result, '<ns2:valid>', '</ns2:valid>');
		$country = $this->string_between_two_string($result, '<ns2:countryCode>', '</ns2:countryCode>');
		$vatnumber = $this->string_between_two_string($result, '<ns2:vatNumber>', '</ns2:vatNumber>');
		$address = $this->string_between_two_string($result, '<ns2:address>', '</ns2:address>');
		$name = $this->string_between_two_string($result, '<ns2:name>', '</ns2:name>');
		$dater = $this->string_between_two_string($result, '<ns2:requestDate>', '</ns2:requestDate>');

		//insert strings in array and replace index keys by name
		$vat = "$country$vatnumber";
		$vars = array($vat, $name, $address, $dater, $valid);
		$new_obj = [];
		$new_obj['vat'] = $vatnumber;
		$new_obj['name'] = $name;
		$new_obj['address'] = $address;
		$new_obj['requestDate'] = $dater;
		$new_obj['countryCode'] = $country;
		$new_obj['valid'] = $valid;
		
		return json_encode($new_obj);
	}
	
	public function recipientinfo($ids)
    {
        $this->db->select('id,name,email,phone');
        $this->db->from('geopos_customers');
        $this->db->where('geopos_customers.id', $ids);
        $query = $this->db->get();
        return $query->row_array();
    }


    public function recipients($ids)
    {
        $this->db->select('id,name,email,phone');
        $this->db->from('geopos_customers');
        $this->db->where_in('id', $ids);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function sales_due($sdate, $edate, $csd, $trans_type, $pay = true, $amount = 0, $acc = 0, $pay_method = '',$note='')
    {
        if ($pay) {
            $this->db->select_sum('total');
            $this->db->select_sum('pamnt');
            $this->db->from('geopos_invoices');
            $this->db->where('DATE(invoicedate) >=', $sdate);
            $this->db->where('DATE(invoicedate) <=', $edate);
            $this->db->where('csd', $csd);
            $this->db->where('status', $trans_type);
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }

            $query = $this->db->get();
            $result = $query->row_array();
            return $result;
        } else {
            if ($amount) {
                $this->db->select('id,tid,total,pamnt');
                $this->db->from('geopos_invoices');
                $this->db->where('DATE(invoicedate) >=', $sdate);
                $this->db->where('DATE(invoicedate) <=', $edate);
                $this->db->where('csd', $csd);
                $this->db->where('status', $trans_type);
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('loc', 0);
                }

                $query = $this->db->get();
                $result = $query->result_array();
                $amount_custom = $amount;

                foreach ($result as $row) {
                    $note.=' #'.$row['tid'];
                    $due = $row['total'] - $row['pamnt'];
                    if ($amount_custom >= $due) {
                        $this->db->set('status', 'paid');
                        $this->db->set('pamnt', "pamnt+$due", FALSE);
                        $amount_custom = $amount_custom - $due;
                    } elseif ($amount_custom > 0 AND $amount_custom < $due) {
                        $this->db->set('status', 'partial');
                        $this->db->set('pamnt', "pamnt+$amount_custom", FALSE);
                        $amount_custom = 0;
                    }

                    $this->db->set('pmethod', $pay_method);
                    $this->db->where('id', $row['id']);
                    $this->db->update('geopos_invoices');

                    if ($amount_custom == 0) break;

					}
					$this->db->select('id,holder');
					$this->db->from('geopos_accounts');
					$this->db->where('id', $acc);
					$query = $this->db->get();
					$account = $query->row_array();

          $data = array(
            'acid' => $account['id'],
            'account' => $account['holder'],
            'type' => 'Income',
            'cat' => 3,
            'credit' => $amount,
            'payer' => $this->lang->line('Bulk Payment Invoices'),
            'payerid' => $csd,
            'method' => $pay_method,
            'date' => date('Y-m-d'),
            'eid' => $this->aauth->get_user()->id,
            'tid' => 0,
            'note' => $note,
            'loc' => $this->aauth->get_user()->loc
        );

        $this->db->insert('geopos_transactions', $data);
        $tttid = $this->db->insert_id();
		            $this->db->set('lastbal', "lastbal+$amount", FALSE);
                    $this->db->where('id', $account['id']);
                    $this->db->update('geopos_accounts');

            }

        }
    }


}