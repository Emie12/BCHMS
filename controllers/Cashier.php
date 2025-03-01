<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cashier extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	

    function __construct(){
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('auth_helper');
        check_session_and_redirect();
        date_default_timezone_set('Asia/Manila');

        $this->load->helper('url');
        $this->load->model('users_model');
    }

	public function index()
	{
		$data['page'] = 'index';

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/index', $data);
		$this->load->view('cashier/footer');
	}



	public function student_fee($school_year = '')
	{
		$data['page'] = 'student_fee';

		// $data['students'] = $this->users_model->getdata('students_tb', null, null, $where);
		$data['students'] = $this->users_model->getstudentfees();

		$yearfrom = (int)date('Y');
		$yearto = (int)date('Y') + 1;

		if ($school_year <> '') {			
			$where = array(	'fee_tb_sy' => $school_year);
		} else {
			$where = array(	'fee_tb_sy' => $yearfrom.'-'.$yearto);
			$school_year = $yearfrom.'-'.$yearto;
		}
		
		
		$newstudent_data = [];

		foreach ($data['students'] as $key => $value) {

			$where1 = array(	'student_id' => $value->id,	'section_code' => $value->stud_section_code,);
			$paid = $this->users_model->getdata('student_fees', null, null, $where1, ' COALESCE(SUM(student_fees.student_fee_amount), 0) AS total_paid, student_id');

			$newstudent_data[$value->id] = $paid[0]->total_paid;
		}

		$data['newstudent_data'] = $newstudent_data;
		
		$data['fees'] = array();
		$fee = $this->users_model->getfees();

		// show_data($fee); exit;

		foreach ($fee as $key => $value) {
			$data['fees'][ $value->level_id] = $value;
		}

		$data['school_year'] = $school_year;

		// show_data($data['students']); 
		// show_data($data['fees']); 
		// exit;

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/payment', $data);
		// $this->load->view('cashier/student_fee', $data);
		$this->load->view('cashier/footer');
	}

    public function addpayment() {
    header('Content-Type: application/json');

    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log received POST data
    log_message('debug', 'Received payment request: ' . json_encode($_POST));

    // Retrieve POST data
    $student_id = $this->input->post('student_id');
    $section_code = $this->input->post('section_code');
    $amount = $this->input->post('student_fee_amount');
    $remarks = $this->input->post('student_fee_remarks');
    $school_year = $this->input->post('student_fees_sy');

    // Check for missing fields
    if (empty($student_id) || empty($section_code) || empty($amount) || empty($remarks) || empty($school_year)) {
        log_message('error', 'Missing required fields in addpayment');
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        return;
    }

    // Ensure amount is numeric
    if (!is_numeric($amount)) {
        log_message('error', 'Invalid amount: ' . $amount);
        echo json_encode(["status" => "error", "message" => "Invalid payment amount"]);
        return;
    }

    // Prepare data for insertion
    $payment_data = [
        'student_id' => $student_id,
        'section_code' => $section_code,
        'student_fee_amount' => $amount,
        'student_fee_remarks' => $remarks,
        'student_fee_date' => date('Y-m-d H:i:s'),
        'student_fees_sy' => $school_year
    ];

    // Insert into database
    if ($this->users_model->insertpayment($payment_data)) {
        log_message('debug', 'Payment inserted successfully for student_id: ' . $student_id);

        // Update status if fully paid
        if ($remarks == 'Paid') {
            if ($this->users_model->updateFeeStatus($student_id, $section_code, 'Paid')) {
                log_message('debug', 'Fee status updated to Paid for student_id: ' . $student_id);
            } else {
                log_message('error', 'Failed to update fee status for student_id: ' . $student_id);
            }
        }

        echo json_encode(["status" => "success"]);
    } else {
        log_message('error', 'Database insert failed for student_id: ' . $student_id);
        echo json_encode(["status" => "error", "message" => "Database insert failed"]);
    }
}


	public function addfee_tb() {

		$fees['fee_tb_id'] = $_POST['id'];;
		$fees['fee_description'] = $_POST['desc'];
		$fees['fee_amount'] = $_POST['amnt'];

        $query1 = $this->users_model->insert('fees', $fees);
        
        if ($query1) {

            echo true;
			
        } else {        	
            echo false;
        }              

    }



	public function addfees() {

		$fee_tb['level_id'] = $_POST['level_id'];
		$fee_tb['fee_tb_sy'] = $_POST['fee_tb_sy'];
		$fee_tb['fee_tb_description'] = $_POST['fee_tb_description'];		
        $fee_tb['user_id'] = $this->session->user['user_id'];

        $query = $this->users_model->insertreturnid('fee_tb', $fee_tb);
        
        if ($query) {

			$fee_amount = count($_POST['fee_amount']);

			if ($fee_amount > 0) {

				for ($i=1; $i <= $fee_amount; $i++) { 

					$fees['fee_tb_id'] = $query;
					$fees['fee_description'] = $_POST['fee_description'][$i];
					$fees['fee_amount'] = $_POST['fee_amount'][$i];

			        $query1 = $this->users_model->insert('fees', $fees);
				}

	            echo true;
			}
        } else {        	
            echo false;
        }              

    }




	public function getfees() {

        $where = array('fee_tb_id' => $_POST['id']);
		$data = $this->users_model->getdata('fees', null, null, $where);

		echo json_encode($data);          

    }



	public function payment($school_year = '')
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'payment';
		$where = null;

		$yearfrom = (int)date('Y');
		$yearto = (int)date('Y') + 1;


		$data['students'] = $this->users_model->getdata('students_tb', null, null, $where);


		$join = array(	'fee_tb' => 'fee_tb.fee_tb_id = fees.fee_tb_id');

		if ($school_year <> '') {			
			$where = array(	'fee_tb_sy' => $school_year);
		} else {
			$where = array(	'fee_tb_sy' => $yearfrom.'-'.$yearto);
		}

		$data['school_year'] = $school_year;
		$data['fees'] = $this->users_model->getdata('fees', $join , null, $where);

		show_data($data['fees']);

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/payment', $data);
		$this->load->view('cashier/footer');
	}




	public function getpayments() {

	    // $schoolyear	= '2024-2025';
	    // $id			= 1;
	    // $level			= 7;

	    $schoolyear	= $_POST['schoolyear'];
	    $id		= $_POST['id'];
	    $level		= $_POST['level'];

		$where = array(	'student_fees_sy' => $schoolyear, 'student_id' => $id);
		$query = $this->users_model->getdata('student_fees', null, null, $where);


		$join2 = array(	'fees' => 'fee_tb.fee_tb_id = fees.fee_tb_id');

		$where2 = array('fee_tb_sy' => $schoolyear, 'level_id' => $level);
		$query2 = $this->users_model->getdata('fee_tb', $join2, null, $where2);

        echo json_encode(array('student_fee' => $query, 'fees' => $query2));

    }




	public function payment_list()
	{
		$data['page'] = 'payment_list';

		$join = array(	'level_tb' => 'level_tb.id = fee_tb.level_id');

		$data['level_tb'] = $this->users_model->getdata('fee_tb', $join);

		$data['levels'] = $this->users_model->getdata('level_tb');
		$data['fees'] = array();
		$fee = $this->users_model->getfees();

// show_data($data['level_tb']);
		foreach ($fee as $key => $value) {
			$data['fees'][ $value->level_id] = $value;
		}


// show_data($data['fees']); exit;
		// $totalfee = (array_key_exists(2, (array)$fees)) ? $fees[2]->total_fee : '0.00';

		// show_data($data['fees']); 
		// show_data(array_key_exists(6, $data['fees'])); exit;

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/payment_list', $data);
		$this->load->view('cashier/footer');
	}




	public function report($school_year = '') {
		$data['page'] = 'report';

		// $data['students'] = $this->users_model->getdata('students_tb', null, null, $where);

		$yearfrom = (int)date('Y');
		$yearto = (int)date('Y') + 1;
		$where = null;
		$where2 = null;
		$data['monthyear'] = '';

		if ($school_year <> '') {			
			$where2 = array("DATE_FORMAT(student_fee_date, '%Y-%m') = '$school_year'" => '');
			$data['monthyear'] = date('F Y', strtotime($school_year));
		} else {
			$school_year = $yearfrom.'-'.$yearto;
		}
		


		$data['students'] = $this->users_model->getstudentfees($where2);
		
		$total_paid = 0;

		foreach ($data['students'] as $key => $value) {
			$total_paid += $value->total_paid;
		}
		
		$data['total_paid'] = number_format($total_paid, 2);

		$data['fees'] = array();
		$fee = $this->users_model->getfees();

		foreach ($fee as $key => $value) {
			$data['fees'][ $value->level_id] = $value;
		}

		$data['school_year'] = $school_year;


		$data['total_amount'] = $this->users_model->getsumamount($where2);

		// show_data($data); exit;

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/report', $data);
		$this->load->view('cashier/footer');
	}





	public function updateattendance()
	{
		if ($_POST['attendance'] == 'true') {
			$data[$_POST['session']] = 1;
		} else {
			$data[$_POST['session']] = 0;
		}

        $where = array(
        	'class_id' => $_POST['class_id'], 
        	'student_id' => $_POST['student_id'], 
        	'class_attendance_date' => $_POST['date']);   

        $query = $this->users_model->getdataid('class_attendance', $where);

        if ($query) {

        	$query1 = $this->users_model->updatedata('class_attendance', 'class_attendance_id', $query['class_attendance_id'], $data);

	        if($query1){
	            echo json_encode($query);
	        }
	        else{
	            echo false;
	        }

        } else {

	        $data['class_id'] 	= $_POST['class_id'];
	        $data['student_id'] = $_POST['student_id'];
	        $data['class_attendance_date'] 	= $_POST['date'];

	        $query1 = $this->users_model->insert('class_attendance', $data);

	        if($query1){
	            echo true;
	        }
	        else{
	            echo false;
	        }


        }
	}









	public function attendance($class_id, $date = null)
	{
        //e figure out nga dapat and makuha nga students kay mag base sa class id
		$data['page'] = 'attendance';

		if ($date <> null) {			
			$where = array(	'class_attendance.class_id' => $class_id, 'class_attendance_date');
		} else {
			$where = array(	'class_attendance.class_id' => $class_id);	
		}

		$join = array(	'class_attendance' => 'class_attendance.student_id = students_tb.id', 
						'classes' => 'classes.class_id = class_attendance.class_id');

		$data['students'] = $this->users_model->getdata('students_tb', $join, null, $where);

		// show_data($data); exit;

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/attendance', $data);
		$this->load->view('cashier/footer');
	}



	public function updategrade() {

        // $schedule_id = 2;
        // $student_id = 11;
        // 
        // 
        $schedule_id = $_POST['schedule_id'];
        $student_id = $_POST['student_id'];
        $grade['student_grade_q1'] = (($_POST['q1'] == 0) || ($_POST['q1'] == '') )? null : $_POST['q1'];
        $grade['student_grade_q2'] = (($_POST['q2'] == 0) || ($_POST['q2'] == '') )? null : $_POST['q2'];
        $grade['student_grade_q3'] = (($_POST['q3'] == 0) || ($_POST['q3'] == '') )? null : $_POST['q3'];
        $grade['student_grade_q4'] = (($_POST['q4'] == 0) || ($_POST['q4'] == '') )? null : $_POST['q4'];

        $where = array('schedule_id' => $schedule_id, 'student_id' =>$student_id);        

        $query = $this->users_model->getdataid('student_grade', $where);



        if ($query) {

        	$query1 = $this->users_model->updatedata('student_grade', 'student_grade_id', $query['student_grade_id'], $grade);


	        if($query1){
	            echo true;
	        }
	        else{
	            echo false;
	        }

        } else {

	        $grade['schedule_id'] = $_POST['schedule_id'];
	        $grade['student_id'] = $_POST['student_id'];

	        $query1 = $this->users_model->insert('student_grade', $grade);

	        if($query1){
	            echo true;
	        }
	        else{
	            echo false;
	        }


        }
    }



	public function section()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'section';

		$data['sections'] = $this->users_model->getdata('section_tb');
		$data['levels'] = $this->users_model->show('level_tb');
		$data['subjects'] = $this->users_model->show('subject_tb');

		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/section', $data);
		$this->load->view('cashier/footer');
	}



	public function class()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'class';
		$data['teachers'] = $this->users_model->getdata('teachers');

		$data['sections'] = $this->users_model->getdata('section_tb');
		$data['levels'] = $this->users_model->getdata('level_tb');
		$data['subjects'] = $this->users_model->getdata('subject_tb');


		$join = array(	'section_tb' => 'classes.section_id = section_tb.section_code', 
						'teachers' => 'classes.teacher_id = teachers.teacher_id');

		$data['classes'] = $this->users_model->getdata('classes', $join);


		$this->load->view('cashier/header',$data);
		$this->load->view('cashier/class', $data);
		$this->load->view('cashier/footer');
	}







	public function get_sections() {
        // $route_from = 1;
        $level_id = $_POST['year_id'];
        $column = 'level';
        $table = 'section_tb';

        $where = array($column => $level_id);
        $query = $this->users_model->getdata($table, null, null, $where);

	    $options = '';
        if (count($query) > 0) {
			foreach ($query as $key => $value) {
				$options .= '<option value="' . $value->section_code . '">' . $value->section. '</option>';
			}
        }
        echo ($options);
	}


	public function insertclass() {



        $data['class_sy'] = $_POST['class_sy'];
        $data['section_id'] = $_POST['section_id'];
        $data['teacher_id'] = $_POST['teacher_id'];
       
        $query = $this->users_model->insert('classes', $data);

        if($query){

            echo true;
        }
        else{
            echo false;
        }

               

    }


	public function insertsection() {

        $data['section_code'] 	= 'SEC' . date('Y') . rand(100000, 999999);
        $data['level'] 			= $_POST['level'];
        $data['section'] 		= $_POST['section'];
       
        $query = $this->users_model->insert('section_tb', $data);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }




	public function insertlevel() {

	    $data['level_code'] = 'LVL' . date('Y') . rand(100000, 999999);
	    $data['level'] 		= $_POST['level'];
       
        $query = $this->users_model->insert('level_tb', $data);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }

	public function insertsubject() {

	    $data['subject_code'] = 'SUB' . date('Y') . rand(100000, 999999);
	    $data['subject'] 		= $_POST['subject'];
       
        $query = $this->users_model->insert('subject_tb', $data);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }




	public function insertschedule() {

	    $data['subject_id'] 		= $_POST['subject_id'];
	    $data['teacher_id'] 		= $_POST['teacher_id'];
	    $data['schedule_start'] 		= $_POST['schedule_start'];
	    $data['schedule_end'] 		= $_POST['schedule_end'];
	    $data['class_id'] 		= $_POST['class_id'];
       
        $query = $this->users_model->insert('schedules', $data);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }




    



	public function deletedata() {

	    $table	= $_POST['table'];
	    $column	= $_POST['column'];
	    $id		= $_POST['id'];
       
        $query = $this->users_model->deletedata($table, $column, $id);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }


	public function getbyid() {

	    // $table	= 'teachers';
	    // $column	= 'teacher_id';
	    // $id		= 2;

	    $table	= $_POST['table'];
	    $column	= $_POST['column'];
	    $id		= $_POST['id'];
       
        $query = $this->users_model->getbyid($table, $column, $id);

        echo json_encode($query);

    }


	public function getschedules() {

	    $table	= $_POST['table'];
	    $column	= $_POST['column'];
	    $id		= $_POST['id'];

		$join = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
						'teachers' => 'schedules.teacher_id = teachers.teacher_id');

		$where = array(	$column => $id);

		$query = $this->users_model->getdata('schedules', $join, null, $where);

        echo json_encode($query);

    }



	public function insert() {


		// echo "<pre>";
		// echo print_r($_POST);
		// echo "</pre>";
		// exit;


        $table = $_POST['tableName'];

        if(!empty($_FILES['upload']['name'])){
            $config['upload_path'] = './assets/admin/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('upload')){


                $uploadData = $this->upload->data();
                $filename = $uploadData['full_path'];
                $filename2 = $uploadData['file_name'];

	            $imageData = file_get_contents($filename);


		        $user['user_name'] 		= $_POST['student_username'];
		        $user['user_password'] 	= $_POST['student_password'];
		        $user['user_type'] 		= 'student';
		        // $user['user_profile'] 	= $filename;

		       
		        $userid = $this->users_model->insertreturnid('users_web', $user);

                if($userid){

			        $data['lrn'] = $_POST['student_lrn'];
			        $data['firstname'] = $_POST['firstname'];
			        $data['middlename'] = $_POST['middlename'];
			        $data['lastname'] = $_POST['lastname'];
			        $data['firstname'] = $_POST['firstname'];
			        $data['address'] = $_POST['student_address'];
			        // $data['student_gender'] = $_POST['student_gender'];
			        // $data['student_email'] = $_POST['student_email'];
			        $data['picture'] = $imageData;
			        $data['picture2'] = $filename2;
			        
			        $data['birthday'] = $_POST['student_dob'];
			        $data['mobile_no'] = $_POST['student_contact'];
			        // $data['year_id'] = $_POST['year_id'];
			        $data['stud_section_code'] = $_POST['stud_section_code'];
			        $data['notify_via_sms'] = 1;
			        $data['status'] = 1;			        
			        $data['recipient'] = $_POST['student_guardian'];
			        $data['user_id'] = $userid;
			       
			        $query = $this->users_model->insert($table, $data);

	                if($query){

		                echo true;
	                }
	                else{
		                echo false;
	                }

                }
                else{
	                echo false;
                }

            } else {	                
            	echo false;
            }

        } else {


		        $user['user_name'] 		= $_POST['student_username'];
		        $user['user_password'] 	= $_POST['student_password'];
		        $user['user_type'] 		= 'student';
		        // $user['user_profile'] 	= $filename;

		       
		        $userid = $this->users_model->insertreturnid('users_web', $user);

                if($userid){

			        $data['lrn'] = $_POST['student_lrn'];
			        $data['firstname'] = $_POST['firstname'];
			        $data['middlename'] = $_POST['middlename'];
			        $data['lastname'] = $_POST['lastname'];
			        $data['firstname'] = $_POST['firstname'];
			        $data['birthday'] = $_POST['student_dob'];
			        $data['address'] = $_POST['student_address'];
			        $data['notify_via_sms'] = 1;
			        $data['status'] = 1;			        
			        $data['recipient'] = $_POST['student_guardian'];
			        $data['mobile_no'] = $_POST['student_contact'];
			        $data['stud_section_code'] = $_POST['stud_section_code'];
			        $data['user_id'] = $userid;
			       
			        // $data['student_gender'] = $_POST['student_gender'];
			        // $data['student_email'] = $_POST['student_email'];
			        // $data['picture'] = $imageData;
			        // $data['year_id'] = $_POST['year_id'];
			        $query = $this->users_model->insert($table, $data);

	                if($query){

		                echo true;
	                }
	                else{
		                echo false;
	                }

                }
                else{
	                echo false;
                }
        }
               

    }


	public function insertteacher() {

        $table = $_POST['tableName'];

        if(!empty($_FILES['upload']['name'])){
            $config['upload_path'] = './assets/admin/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('upload')){
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];

		        $user['user_name'] = $_POST['teacher_username'];
		        $user['user_password'] = $_POST['teacher_password'];
		        $user['user_type'] = 'teacher';

		        $userid = $this->users_model->insertreturnid('users_web', $user);

                if($userid){

			        $data['teacher_fullname'] = $_POST['teacher_fullname'];
			        $data['teacher_email'] = $_POST['teacher_email'];
			        $data['teacher_address'] = $_POST['teacher_address'];
			        $data['teacher_gender'] = $_POST['teacher_gender'];
			        $data['teacher_profile'] = $filename;
			        $data['teacher_dob'] = $_POST['teacher_dob'];
			        $data['teacher_contact'] = $_POST['teacher_contact'];
			        $data['user_id'] = $userid;
			       
			        $query = $this->users_model->insert($table, $data);

	                if($query){

		                echo true;
	                }
	                else{
				        $query = $this->users_model->deletedata('users_web', 'user_id', $userid);
		                echo false;
	                }
                }
                else{
	                echo false;
                }

            } else {	                
            	echo false;
            }

        } else {

	        $user['user_name'] = $_POST['teacher_username'];
	        $user['user_password'] = $_POST['teacher_password'];
	        $user['user_type'] = 'teacher';
	       
	        $userid = $this->users_model->insertreturnid('users_web', $user);

            if($userid){

		        $data['teacher_fullname'] = $_POST['teacher_fullname'];
		        $data['teacher_email'] = $_POST['teacher_email'];
		        $data['teacher_address'] = $_POST['teacher_address'];
		        $data['teacher_gender'] = $_POST['teacher_gender'];
		        $data['teacher_dob'] = $_POST['teacher_dob'];
		        $data['teacher_contact'] = $_POST['teacher_contact'];
		        $data['user_id'] = $userid;
		       
		        $query = $this->users_model->insert($table, $data);

                if($query) {
	                echo true;
                }
                else {
			        $query = $this->users_model->deletedata('users_web', 'user_id', $userid);
	                echo false;
                }
            }
            else{
                echo false;
            }
        }
               

    }


	public function insertstudent() {


// 		tableName: students
// student_fullname: 
// student_fullname: 
// student_fullname: 
// student_lrn: 
// student_username: 
// student_password: 
// student_email: 
// student_address: 
// student_dob: 
// student_contact: 
// year_id: Year
// section_id: Section
// student_gender: male





// Full texts
// id
// lrn
// rfid_tag
// birthday
// stud_section_code
// notify_via_sms
// recipient
// mobile_no
// status
// timetapped
// datetapped
// picture






        $table = $_POST['tableName'];

        if(!empty($_FILES['upload']['name'])){
            $config['upload_path'] = './assets/admin/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('upload')){
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];

		        $user['user_name'] = $_POST['teacher_username'];
		        $user['user_password'] = $_POST['teacher_password'];
		        $user['user_type'] = 'student';

		        $userid = $this->users_model->insertreturnid('users_web', $user);

                if($userid){





// address

			        $data['lastname'] = $_POST['teacher_fullname'];
			        $data['middlename'] = $_POST['teacher_email'];
			        $data['firstname'] = $_POST['teacher_address'];
			        $data['lrn'] = strtoupper($_POST['lrn']);
			        $data['teacher_profile'] = $filename;
			        $data['teacher_dob'] = $_POST['teacher_dob'];
			        $data['teacher_contact'] = $_POST['teacher_contact'];
			        $data['user_id'] = $userid;
			       
			        $query = $this->users_model->insert($table, $data);

	                if($query){

		                echo true;
	                }
	                else{
				        $query = $this->users_model->deletedata('users_web', 'user_id', $userid);
		                echo false;
	                }
                }
                else{
	                echo false;
                }

            } else {	                
            	echo false;
            }

        } else {

	        $user['user_name'] = $_POST['teacher_username'];
	        $user['user_password'] = $_POST['teacher_password'];
	        $user['user_type'] = 'teacher';
	       
	        $userid = $this->users_model->insertreturnid('users_web', $user);

            if($userid){

		        $data['teacher_fullname'] = $_POST['teacher_fullname'];
		        $data['teacher_email'] = $_POST['teacher_email'];
		        $data['teacher_address'] = $_POST['teacher_address'];
		        $data['teacher_gender'] = $_POST['teacher_gender'];
		        $data['teacher_dob'] = $_POST['teacher_dob'];
		        $data['teacher_contact'] = $_POST['teacher_contact'];
		        $data['user_id'] = $userid;
		       
		        $query = $this->users_model->insert($table, $data);

                if($query) {
	                echo true;
                }
                else {
			        $query = $this->users_model->deletedata('users_web', 'user_id', $userid);
	                echo false;
                }
            }
            else{
                echo false;
            }
        }
               

    }


	public function updateuser() {

        $table = 'users_web';
        $id = $_POST['update_user_id'];
        $user['user_name'] = $_POST['update_teacher_username'];
        $user['user_password'] = $_POST['update_teacher_password'];
       

        $query = $this->users_model->updatedata('users_web', 'user_id', $id, $user);

        if($query){
        	echo true;
        }
        else{
            echo false;
        }   

    }


    public function updateteacher() {

    	$this->updateuser();

        $id = $_POST['update_teacher_id'];

        if(!empty($_FILES['update_upload']['name'])){
            $config['upload_path'] = './assets/admin/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('update_upload')){
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];

	            $data['teacher_fullname'] 	= $_POST['update_teacher_fullname'];
	            $data['teacher_email'] 		= $_POST['update_teacher_email'];
	            $data['teacher_address'] 	= $_POST['update_teacher_address'];
	            $data['teacher_gender'] 	= $_POST['update_teacher_gender'];
	            $data['teacher_profile'] 	= $filename;
	            $data['teacher_dob'] 		= $_POST['update_teacher_dob'];
	            $data['teacher_contact'] 	= $_POST['update_teacher_contact'];

		        $query = $this->users_model->updatedata('teachers', 'teacher_id', $id, $data);
	           
	            if($query) {
	            	echo true;
	            } else{
	            	echo false;
	            }                

            } else {                    
                echo false;
            }

        } else {
        	$data['teacher_fullname'] 	= $_POST['update_teacher_fullname'];
            $data['teacher_email'] 		= $_POST['update_teacher_email'];
            $data['teacher_address'] 	= $_POST['update_teacher_address'];
            $data['teacher_gender'] 	= $_POST['update_teacher_gender'];
            $data['teacher_dob'] 		= $_POST['update_teacher_dob'];
            $data['teacher_contact'] 	= $_POST['update_teacher_contact'];

	        $query = $this->users_model->updatedata('teachers', 'teacher_id', $id, $data);
           
            if($query) {
            	echo true;
            } else{
            	echo false;
            }                

        }
               

    }






}
