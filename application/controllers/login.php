<?php

class Login extends CI_Controller {
    
    function index() {
        $data['main_content'] = 'login/login_form';
        $this->load->view('includes/template', $data);
    }
    
    function validate_credentials() {
        $this->load->model('membership_model');
        $query = $this->membership_model->validate();
        
        if($query) {
            foreach ($query->result() as $row) {
                $user_id = $row->user_id;                
            }
            
            $data = array(
                'username' => $this->input->post('username'),
                'is_logged_in' => true,
                'user_id' => $user_id);
            
            $this->session->set_userdata($data);
            redirect('main');
        }
        else {
            redirect('main');        
        }
    }
    
    function signup() {
        $data['load_page'] = 'signup_form';
        $data['output'] = '';
        $this->load->view('includes/template', $data);
    }
    
    function create_member() 
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('first_name', 'Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|valid_email|max_length[250]');
        
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[4]|max_length[25]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
        $this->form_validation->set_rules('password2', 'Password Confirmation', 'trim|required|matches[password]');
        
        if ($this->form_validation->run() == FALSE)
        {
            $this->signup();
        }
        else 
        {
            $confirm = $this->load->model('membership_model');
            if ($confirm = $this->membership_model->create_member()) 
            {   
                $data['template'] = $this->confirm_email($confirm);
                $data['load_page'] = 'signup_successful';
                $data['output'] = '';
                
                $this->load->view('includes/template', $data);
            }
            else
            {
                $this->load->view('signup_form');
            }
        }
    }
    
    function confirm_email($confirm) {
        
        /*@todo fix this so it actually sends an email and pulls a email template in */
        
        $template = ('Welcome {USERNAME}!

                        Thank-you for creating an account.

                        Please confirm your account by copy and pasting the link below into your browsers address bar.

                        {SITEPATH}/recieve_confirmation/{EMAIL}/{KEY}

                        Thanks!');
        $template = str_replace('{USERNAME}', $this->input->post('username'), $template);
        $template = str_replace('{EMAIL}', $confirm['email'], $template);
        $template = str_replace('{KEY}', $confirm['key'], $template);
        $template = str_replace('{SITEPATH}', 'http://127.0.0.1/MovieReview_Pro3/index.php/login', $template);
        
        
        //$this->email->from('your@example.com', 'Moovies');
        //$this->email->to($confirm['email']);
        //$this->email->message($template);
        //$this->email->subject('Confirmation Email');
        
        //$this->email->send();
        
        return $template;
    }
    
    function recieve_confirmation() {
        
        $action = array('result' => null);
        $email = $this->uri->segment(3);
        $key = $this->uri->segment(4);
        
        if (empty($email) || empty($key)) {
            $action['result'] = 'error';
            $action['text'] = 'We are missin variables. Please double check your email.';            
        }
        
        if ($action['result'] != 'error') {
            $this->load->model('membership_model');
            $action['text'] = $this->membership_model->check_confirmation($email, $key);    
        }
        
        //@todo go to a page which outputs $action['text'];
        
    }
    
    function logout() {        
        $this->session->sess_destroy();
        redirect('main');
    }
}
?>
