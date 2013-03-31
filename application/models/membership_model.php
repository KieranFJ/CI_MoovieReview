<?php

class Membership_model extends CI_Model {
    
    function validate() {
        
        $this->db->where('username', $this->input->post('username'));
        $this->db->where('password', md5($this->input->post('password')));
        $this->db->select('user_id');
        $query = $this->db->get('membership');
        
        if ($query->num_rows == 1) {
            return $query;
        }
                
    }
    
    function create_member() {
        $new_member_insert_data = array (
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email_address' => $this->input->post('email_address'),
            
            'username' => $this->input->post('username'),
            'password' => md5($this->input->post('password'))
        );
        
        $insert = $this->db->insert('membership', $new_member_insert_data);
        
        $user_id = $this->db->insert_id();
        
        $key = $new_member_insert_data['username'].$new_member_insert_data['email_address'].date('mY');
        $key = md5($key);
        
        $confirm = array (
            'user_id' => $user_id,
            'key' => $key,
            'email' => $new_member_insert_data['email_address']
        );        
        
        $this->db->insert('confirm', $confirm);
        
        return $confirm;
    }
    
    function check_confirmation($email, $key) {
        $this->db->where('email', $email);
        $this->db->where('key', $key);
        $query = $this->db->get('confirm', 1);
        
        if ($query->num_rows == 1) {
            foreach ($query->result() as $row) {
                $user_id = $row->user_id;
                $confirm_id = $row->confirm_id;
            }
            
            
            $update = $this->db->query("UPDATE `membership` SET `active` = 1 WHERE `user_id` = '$user_id' LIMIT 1");
                                       
            $delete = $this->db->query("DELETE FROM `confirm` WHERE `confirm_id` = '$confirm_id' LIMIT 1");
            if ($update && $delete) {
                //@todo success/error reporting here
                return 'Success';    
            }
            else {
                return 'error update';
            }
            
        }
        else {
            return 'Fail num rows';
        }
    }
}
?>
