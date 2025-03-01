<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Guardian extends CI_Controller {

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

        $this->load->helper('url');
            // $this->session->set_userdata('toEnroll', false);
        $this->load->model('users_model');
    }

	public function index()
	{

        //restrict users to go to home if not logged i
		$data['page'] = 'profile';
		$data['to_enroll'] = false;

		$grade = true;

		$where = array(	'students_tb.user_id' => $this->session->user['user_id']);


		// show_data($this->session); exit;


		// if (!empty($this->session->user['user_profile'])) {
		// 	$data['user_profile'] = base_url() . '/assets/img/'.$this->session->user['user_profile'];
		// } else {
		// 	$data['user_profile'] = base_url() . '/assets/img/default.jpg';
		// }
		// 
		$join = array(	'section_tb' => 'section_tb.section_code = students_tb.stud_section_code',
						'classes' => 'classes.section_id = students_tb.stud_section_code',
						'level_tb' => 'level_tb.level = section_tb.level'
	);
		$join2 = array(	'users_web' => 'announcement.user_id = users_web.user_id');

		$select = 'students_tb.* , section_code, level_tb.id as level, section, class_id, teacher_id, class_sy, level_tb.level as levelname';

		$data['student'] = $this->users_model->getdata('students_tb', $join, null, $where, $select);


		$data['announcements'] = $this->users_model->getdata('announcement', $join2);


		$data['teachers'] = $this->users_model->getdata('teachers', null, null, array(	'teacher_id' => $data['student'][0]->teacher_id));

		$where2 = array('MONTH(class_attendance_date)' => date('n'), 'students_tb.user_id' => $this->session->user['user_id']);


		$join6 = array(	'students_tb' => 'class_attendance.student_id = students_tb.id');
		$attendance= $this->users_model->getdata('class_attendance', $join6, null, $where2);

		// schedules

		$student_grade= $this->users_model->getdata('student_grade', null, null, array('student_id' => $data['student'][0]->id));


		// show_data($data['student']);
		foreach ($student_grade as $value) {

			if($value->student_grade_q1 == '') {

				$grade = false;
				break;
			}
			if($value->student_grade_q2 == '') {

				$grade = false;
				break;
			}
			if($value->student_grade_q3 == '') {

				$grade = false;
				break;
			}
			if($value->student_grade_q4 == '') {

				$grade = false;
				break;
			}
		}
		// show_data($grade);

		// exit;

// 		$payment = $this->users_model->getstudentfees(array('student_id' => $data['student'][0]->id, 'student_fees.section_code' => $data['student'][0]->stud_section_code, ));

		$where9 = array(	'student_id' => $data['student'][0]->id, 'section_code' => $data['student'][0]->stud_section_code);
		$payment = $this->users_model->getdata('student_fees', null, null, $where9, ' COALESCE(SUM(student_fees.student_fee_amount), 0) AS total_paid, student_id');

		$getfees = $this->users_model->getfees(array('level_id' => $data['student'][0]->level));

		// show_data($data['student']);
		// exit;
		// show_data($payment);
		// show_data($schedules);
		// exit;
		// 
		$fullpayment = false;
		if ($payment && $getfees) {

			if($getfees[0]->total_fee == $payment[0]->total_paid) {

		$fullpayment = true;
				// if ($grade) {
				// 	$data['to_enroll'] = true;
				// }
			} 	
		}


		$data['payment'] = $payment;
		$data['getfees'] = $getfees;

		$present = 0; 
		$absent = 0; 

		foreach ($attendance as $key => $value) {
			if ($value->class_attendance_am == "1") {
				$present += 0.5; 
			} else {
				$absent += 0.5; 
			}


			if ($value->class_attendance_pm == "1") {
				$present += 0.5; 
			} else {
				$absent += 0.5; 
			}
		}

		$data['attendance'] = array("present" => $present, "absent" => $absent);


		// $where5 = array('class_id' => $data['student'][0]->class_id);
		// $join4 = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id',
		// 				'teachers' => 'schedules.teacher_id = teachers.teacher_id');
		// $order4 = array('schedule_start' => 'asc');
		// $schedules = $this->users_model->getdata('schedules', $join4, $order4, $where5);



		// show_data( $data['student'][0]);


        $where33 = array('level' => $data['student'][0]->levelname);
        $query33 = $this->users_model->getdata('subject_tb', null, null, $where33);
        // $query33 = $this->users_model->getdata('subject_tb');
        $newdata = [];

        //get teacher and schedule by subject id and level
        foreach ($query33 as $key => $value) {
			
	        $this->db->select('*')
	         ->from('schedules')
	         ->join('subject_tb', 'schedules.subject_id = subject_tb.id', 'left') 
	         ->join('teachers', 'schedules.teacher_id = teachers.teacher_id', 'left') 
	         ->join('classes', 'schedules.class_id = classes.class_id') 
	         ->join('section_tb', 'classes.section_id = section_tb.section_code') 
	         
	         ->where('subject_id', $value->id)
	         ->where('schedules.class_id', $data['student'][0]->class_id);
	        $query2 = $this->db->get()->result();

			if ($query2) {
				$newdata[] = $query2[0];
			} else {

				$newdata[] = $value;
			}
        }


		// show_data($newdata);
		// exit;

		// $where4 = array('student_id' => 1);
		// $grade = $this->users_model->getdata('student_grade', null, null, $where4);

		// show_data($data['student']);
		// show_data($data['student'][0]->level);
		// show_data($schedules);
		// exit;
		$grades2 = array();


		foreach ($student_grade as $key => $value) {
			$grades2[$value->schedule_id] = $value;
		}

		$data['schedules'] = $newdata;
		$data['student_grade'] = $grades2;

// 		if ($data['student'][0]->to_enroll == 'Yes') {
//             $this->session->set_userdata('toEnroll', true);
// 		} else {
//             $this->session->set_userdata('toEnroll', false);
// 		}
		
		$data['to_enroll'] = false;

		if ($grade == true && $fullpayment == true) {
			$data['to_enroll'] = true;
		}
		
		if ($data['student'][0]->to_enroll == 'Yes') {
            $this->session->set_userdata('toEnroll', true);

				
		} else {
            $this->session->set_userdata('toEnroll', false);
		}




		// var_dump($data['schedules']);
		$this->load->view('guardian/header',$data);
		$this->load->view('guardian/index', $data);
		$this->load->view('guardian/footer');
	}


    public function updateattendanceinfo(){

		$date = DateTime::createFromFormat('Y-m', $_POST['date']);
		$month = $date->format('m');

		$where2 = array('MONTH(class_attendance_date)' => $month, 'student_id' => 1);
		$attendance= $this->users_model->getdata('class_attendance', null, null, $where2);


		$present = 0; 
		$absent = 0; 

		foreach ($attendance as $key => $value) {
			if ($value->class_attendance_am == "1") {
				$present += 0.5; 
			} else {
				$absent += 0.5; 
			}


			if ($value->class_attendance_pm == "1") {
				$present += 0.5; 
			} else {
				$absent += 0.5; 
			}
		}

        echo json_encode(array("present" => $present, "absent" => $absent));

    }



    public function updateinfo(){

        $id = $this->session->user['user_id'];
        $user['user_email'] = $_POST['user_email'];
        $user['user_contact'] = $_POST['user_contact'];

        $query = $this->users_model->updateuser($user, $id);

        if ($query) {
            $data = $this->users_model->getuser($id);
            $this->session->set_userdata('user', $data);
        } 
    }

    public function upload(){
 
        if(!empty($_FILES['upload']['name'])){
            $config['upload_path'] = './assets/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('upload')){
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];
 
                //set file data to insert to database
		        $id = $this->session->user['user_id'];
                $user['user_profile'] = $filename;

		        $query = $this->users_model->updateuser($user, $id);

                if($query){

		            $data = $this->users_model->getuser($id);
		            $this->session->set_userdata('user', $data);
	                echo 'success';
                }
                else{
	                echo 'failed';
                }
 
            }else{
	            echo 'failed';
            }
        }else{
            echo 'Error Uploading!';
        }
               
        }


	public function getpayments() {

	    // $schoolyear	= '2024-2025';
	    // $id			= 1;

	    $schoolyear	= $_POST['schoolyear'];
	    $id		= $_POST['id'];
	    $level		= $_POST['level'];

		$where = array(	'student_fees_sy' => $schoolyear, 'student_id' => $id);
		$query = $this->users_model->getdata('student_fees', null, null, $where);


		$join2 = array(	'fee_tb' => 'fee_tb.fee_tb_id = fees.fee_tb_id');

		$where2 = array('fee_tb_sy' => $schoolyear, 'level_id' => $level);
		$select = '* , FORMAT(fee_amount, 2) AS fee_amount2';
		$query2 = $this->users_model->getdata('fees', $join2, null, $where2, $select);

		$select2 = 'FORMAT(SUM(fee_amount), 2) AS sum_amount';
		$query3 = $this->users_model->getdata('fees', $join2, null, $where2, $select2);

        echo json_encode(array('student_fee' => $query, 'fees' => $query2, 'sum' => $query3[0]->sum_amount));

    }




	public function updatetoenroll() {

        $id = $_POST['id'];
        $user['to_enroll'] = 'Yes';

        $query = $this->users_model->updatedata('students_tb', 'id', $id, $user);

        if($query){
            $this->session->set_userdata('toEnroll', true);
        	echo true;
        }
        else{
            echo false;
        }   

    }


	public function getannouncement() {
	    $id		= $_POST['id'];

		$where = array(	'announcement_id' => $id);
        $query = $this->users_model->getbyid('announcement', 'announcement_id', $id);
        echo json_encode($query);

    }

}
