<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mailflow_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function searchLeadsEmails($leadSource=[], $assignedStaff=[], $leadCountry=[],$leadGroups=[])
    {

        $this->db->from('tblleads');

        if (!empty($leadSource)) {
            $this->db->where_in('source', $leadSource);
        }

        if (!empty($assignedStaff)) {
            $this->db->where_in('assigned', $assignedStaff);
        }

        if (!empty($leadCountry)) {
            $this->db->where_in('country', $leadCountry);
        }

        if (!empty($leadGroups)) {
            $this->db->where_in('status', $leadGroups);
        }

        $query = $this->db->get();

        $emails = array_column($query->result_array(), 'email');
        $emails = array_filter($emails, 'strlen');

        return array_unique($emails);
    }

    public function searchCustomersEmails($customerStatus = 'active', $customerGroups=[], $customerCountries=[])
    {

        $status = '1';

        if ($customerStatus == 'inactive') {
            $status = '0';
        }

        $this->db->select('ct.email')
            ->from('tblclients c')
            ->join('tblcontacts ct', 'c.userid = ct.userid')
            ->join('tblcustomer_groups cg', 'c.userid = cg.customer_id');

        if (!empty($customerStatus)) {
            $this->db->where('c.active', $status);
        }

        if (!empty($customerGroups)) {
            $this->db->where_in('cg.groupid', $customerGroups);
        }

        if (!empty($customerCountries)) {
            $this->db->where_in('c.country', $customerCountries);
        }

        $query = $this->db->get();
        $emails = array_column($query->result_array(), 'email');
        $emails = array_filter($emails, 'strlen');

        return array_unique($emails);
    }
    
    public function add($data)
    {
        $this->db->insert(db_prefix() . 'mailflow_newsletter_history', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'mailflow_newsletter_history')->row();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mailflow_newsletter_history');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
}
