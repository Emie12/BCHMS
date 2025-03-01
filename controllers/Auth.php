<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Auth extends CI_Controller {
 
    function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('users_model');
        date_default_timezone_set('Asia/Manila');
    }

    public function index(){
        //load session library
        $this->load->library('session');

        //restrict users to go back to login if session has been set
        if($this->session->userdata('user')){

            if ($this->session->user['user_type'] == 'admin') {
                redirect('admin');
            } elseif ($this->session->user['user_type'] == 'student') {
                redirect('guardian');

            } elseif ($this->session->user['user_type'] == 'teacher') {
                redirect('teacher');

            } elseif ($this->session->user['user_type'] == 'cashier') {

                if ($this->session->user['user_status'] == 1) {

                    redirect('cashier');
                 } else {
                    // var_dump($this->session->user['user_status']);
                    $this->logout();
                 }
            } 
        }
        else{
             $this->load->view('login');
        }
    }

    public function login(){
        //load session library
        $this->load->library('session');

        $user_name = $_POST['user_name'];
        $user_password = $_POST['user_password'];

        $data = $this->users_model->login($user_name, $user_password);

        if($data){
            $query = $this->users_model->updatedata('users_web', 'user_id', $data['user_id'], array('user_lastlog' => date('Y-m-d H:i:s')));
            
            $this->session->set_userdata('user', $data);
            $this->session->set_userdata('class_sy', date('Y') . '-' . (date('Y')  + 1));


            if ($this->session->user['user_type'] == 'admin') {
                if ($this->checkstudents()) {
                    redirect('admin');
                }
                
            } elseif ($this->session->user['user_type'] == 'student') {
                redirect('guardian');

            } elseif ($this->session->user['user_type'] == 'teacher') {
                redirect('teacher');

            } elseif ($this->session->user['user_type'] == 'cashier') {
                 if ($this->session->user['user_status'] == 1) {

                    redirect('cashier');
                 } else {
                    // var_dump($this->session->user['user_status']);
                    // $this->logout();
                $this->session->unset_userdata('user');
                header('location:'.base_url().$this->index());
                $this->session->set_flashdata('error','Inactive user account!');
                 }
            } 

        }
        else{
                header('location:'.base_url().$this->index());
                $this->session->set_flashdata('error','Invalid login. User not found');
        }
    }

    public function logout(){
        //load session library
        $this->load->library('session');
        $this->session->unset_userdata('user');
        redirect('auth');
    }


    public function checkstudents() {

        $this->load->helper('auth_helper');

        $where1 = array('user_id' => '');
        $array1 = $this->users_model->getdata('students_tb', null, null, $where1);

        if (count($array1) > 0) {
            foreach ($array1 as $key => $value) {

                $data['user_name'] = $value->firstname;
                $data['user_password'] = $value->lastname;
                $data['user_type'] = 'student';
                $data['user_status'] = 1;
               
                // $query = $this->users_model->insert('users_web', $data);

                $userid = $this->users_model->insertreturnid('users_web', $data);

                $user['user_id'] = $userid;
                $query = $this->users_model->updatedata('students_tb', 'id', $value->id, $user);
            }
        }
        return true;
    }
 
}