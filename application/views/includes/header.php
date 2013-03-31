<!DOCTYPE htem>

<html lang="en">
    
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
    <title>untitled</title>
    <link rel="stylesheet" href="<?php echo base_url();?>css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
</head>
<body>
<div id="wrapper">
    <div id="header">
        Header image Here
        <div id="loginBox">
            <?php     
            if ($this->session->userdata('is_logged_in')) {
                echo 'Logged In';
                echo anchor('login/logout' , 'Logout');
            }
            else {
                echo form_open('login/validate_credentials');
                echo form_input('username', 'Username');
                echo form_password('password', 'Password');
                echo form_submit('submit', 'Login');

                echo anchor('login/signup', 'Create Account');
            }
            
            ?>
        </div>
    </div>