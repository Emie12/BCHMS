<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teacher extends CI_Controller {

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

		$join = array(	'section_tb' => 'classes.section_id = section_tb.section_code', 
						'teachers' => 'classes.teacher_id = teachers.teacher_id',
						'users_web' => 'teachers.user_id = users_web.user_id');

		$where = array(	'teachers.user_id' => $this->session->user['user_id']);

		$data['classes'] = $this->users_model->getdata('classes', $join, null, $where);

        $this->session->set_userdata('teacher_id', $data['classes'][0]->teacher_id);

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/index', $data);
		$this->load->view('teacher/footer');
	}



	public function dashboard($year_section = null, $school_year = null)
	{
		$data['page'] = 'index';
		$data['year_section'] = $year_section;
		$data['school_year'] = $school_year;

		$join = array(	'section_tb' => 'classes.section_id = section_tb.section_code', 
						'teachers' => 'classes.teacher_id = teachers.teacher_id',
						'users_web' => 'teachers.user_id = users_web.user_id');

		$where = array(	'teachers.user_id' => $this->session->user['user_id'], 'section_id' => $year_section);

		$data['classes'] = $this->users_model->getdata('classes', $join, null, $where);

		// show_data($data); exit;

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/dashboard', $data);
		$this->load->view('teacher/footer');
	}





	public function schedule($teacher_id, $class_id, $level='')
	{
		$data['page'] = 'teacher';

		$data['teachers'] = $this->users_model->getdata('teachers');
		$data['page'] = 'index';

		// $join = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
		// 				'teachers' => 'schedules.teacher_id = teachers.teacher_id', 
		// 				'classes' => 'schedules.class_id = classes.class_id', 
		// 				'section_tb' => 'classes.section_id = section_tb.section_code');

		// // $where = array(	'schedules.class_id' => $class_id);
		
		// $where = array(	'schedules.class_id' => $class_id , 'section_tb.level' => $level);

		// $data['schedules'] = $this->users_model->getdata('schedules', $join, null, $where);





        $where = array('level' => $level);
        $query = $this->users_model->getdata('subject_tb', null, null, $where);
        $newdata = [];

        //get teacher and schedule by subject id and level
        foreach ($query as $key => $value) {
			
	        $this->db->select('*')
	         ->from('schedules')
	         ->join('subject_tb', 'schedules.subject_id = subject_tb.id', 'left') 
	         ->join('teachers', 'schedules.teacher_id = teachers.teacher_id', 'left') 
	         ->join('classes', 'schedules.class_id = classes.class_id') 
	         ->join('section_tb', 'classes.section_id = section_tb.section_code') 
	         
	         ->where('subject_id', $value->id)
	         ->where('schedules.class_id', $class_id);
	        $query2 = $this->db->get()->result();

			if ($query2) {
				$newdata[] = $query2[0];
			} else {

				$newdata[] = $value;
			}
        }

		$data['schedules'] = $newdata;



		// show_data($data['schedules'] ); exit;

        $this->session->set_userdata('class_id', $class_id);

        if ($data['schedules']) {

        $this->session->set_userdata('class_sy', $data['schedules'][0]->class_sy);

        }
		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/teacher', $data);
		$this->load->view('teacher/footer');
	}




	public function schedule2($teacher_id, $class_id, $level='')
	{
		$data['page'] = 'teacher';

		$data['teachers'] = $this->users_model->getdata('teachers');
		$data['page'] = 'index';

		// $join = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
		// 				'teachers' => 'schedules.teacher_id = teachers.teacher_id', 
		// 				'classes' => 'schedules.class_id = classes.class_id', 
		// 				'section_tb' => 'classes.section_id = section_tb.section_code');

		// $where = array(	'schedules.class_id' => $class_id , 'section_tb.level' => $level);

		// $data['schedules'] = $this->users_model->getdata('schedules', $join, null, $where);

        $where = array('level' => $level);
        $query = $this->users_model->getdata('subject_tb', null, null, $where);
        $newdata = [];

        //get teacher and schedule by subject id and level
        foreach ($query as $key => $value) {

        	if ($value->has_grade == 'No') {
        		continue;
        	}
			
	        $this->db->select('*')
	         ->from('schedules')
	         ->join('subject_tb', 'schedules.subject_id = subject_tb.id', 'left') 
	         ->join('teachers', 'schedules.teacher_id = teachers.teacher_id', 'left') 
	         ->join('classes', 'schedules.class_id = classes.class_id') 
	         ->join('section_tb', 'classes.section_id = section_tb.section_code') 
	         
	         ->where('subject_id', $value->id)
	         ->where('schedules.class_id', $class_id);
	        $query2 = $this->db->get()->result();

			if ($query2) {
				$newdata[] = $query2[0];
			} else {

				$newdata[] = $value;
			}
        }

		$data['schedules'] = $newdata;
        $this->session->set_userdata('class_id', $class_id);

        if ($data['schedules']) {

        $this->session->set_userdata('class_sy', $data['schedules'][0]->class_sy);

        }
		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/teacher2', $data);
		$this->load->view('teacher/footer');
	}



	public function grade($schedule_id, $section_code)
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'grade';
		$where = array(	'stud_section_code' => $section_code);

		$data['students'] = $this->users_model->getdata('students_tb', null, null, $where);

		$where2 = array('schedule_id' => $schedule_id);
		$grades = $this->users_model->getdata('student_grade', null, null, $where2);
		$grades2 = array();


		// show_data($grades);
		// exit;
		foreach ($grades as $key => $value) {

			$grades2[$value->student_id] = $value;
		}

		$data['student_grade'] = $grades2;
		$data['schedule_id'] = $schedule_id;

		// echo "<pre>";
		// // echo print_r($data['students']);
		// echo print_r($data['student_grade']);
		// // echo print_r($data['student_grade'][1]->student_grade_q1);
		// echo "</pre>"; exit;

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/grade', $data);
		$this->load->view('teacher/footer');
	}

	public function attendance_today($class_id, $section_code)
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'grade';
		$where = array(	'stud_section_code' => $section_code);

		$data['students'] = $this->users_model->getdata('students_tb', null, null, $where);

		$where2 = array('class_id' => $class_id, 'class_attendance_date' => date('Y-m-d'));
		$class_attendance = $this->users_model->getdata('class_attendance', null, null, $where2);
		$class_attendances = array();

		foreach ($class_attendance as $key => $value) {

			$class_attendances[$value->student_id] = $value;
		}

		$data['class_attendances'] = $class_attendances;
		$data['class_id'] = $class_id;

		// show_data($data['class_attendances']); exit;


		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/attendance_today', $data);
		$this->load->view('teacher/footer');
	}




	public function report($level=null, $section=null)
	// public function report($level=null, $section=null)
	{

		$data['page'] = 'report';
		$data['teachers'] = $this->users_model->getdata('teachers');

		$data['section_code'] = $this->users_model->getdata('section_tb');
		$data['levels'] = $this->users_model->getdata('level_tb');


		$join1 = array(	'section_tb' => 'classes.section_id = section_tb.section_code', 
						'teachers' => 'classes.teacher_id = teachers.teacher_id');
		$where1 = array(	'teachers.user_id' => $this->session->user['user_id']);
		$class = $this->users_model->getdata('classes', $join1, null, $where1);



		$class_id = $class[0]->class_id;
		$section_code = $class[0]->section_code;


		$value12 = array();
		$wherein = array();
		foreach ($class as $key => $value) {
			$wherein['stud_section_code'][] = $value->section_code;
		}

		$join = array(	'rfid_tap_logstb' => 'rfid_tap_logstb.lrn = students_tb.lrn',
						'section_tb' => 'section_tb.section_code = students_tb.stud_section_code');

		$where = array();
		$data['levelby'] = '';
		$data['stud_section_code'] = '';
		$data['dateFrom'] = date('Y-m-d');
		$data['dateTo'] = date('Y-m-d');

		if ($this->input->get('levelby')) {		    
			$where['level'] = $this->input->get('levelby');
			$data['levelby'] = $this->input->get('levelby');
		}
		
		if ($this->input->get('sectionby')) {		    
			$where['stud_section_code'] = $this->input->get('sectionby');
			$data['sectionby'] = $this->input->get('sectionby');
		}
		
		if ($this->input->get('dateFrom')) {
		    
			$where['date >='] = $this->input->get('dateFrom');
			$data['dateFrom'] = $this->input->get('dateFrom');
		}
		
		if ($this->input->get('dateTo')) {
		    
			$where['date <='] = $this->input->get('dateTo');
			$data['dateTo'] = $this->input->get('dateTo');
		}

		// $order = array('lastname' => 'asc', 'firstname' => 'asc');
		$order = array('rfid_tap_logstb.date' => 'desc', 'rfid_tap_logstb.time' => 'desc');
		
		// $where['section_code'] = $section_code;
		$data['students'] = $this->users_model->getdatawherein('students_tb', $join, $order, $where, $wherein);
		// $data['students'] = $this->users_model->getdata('students_tb', $join, $order, $where);

		// show_data($data['students']); exit;

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/report', $data);
		$this->load->view('teacher/footer');
	}





	public function workload()
	{
		$data['page'] = 'workload';

		$data['teachers'] = $this->users_model->getdata('teachers');

		$join = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
						'teachers' => 'schedules.teacher_id = teachers.teacher_id', 
						'classes' => 'schedules.class_id = classes.class_id', 
						'section_tb' => 'classes.section_id = section_tb.section_code');


		$where = array(	'schedules.teacher_id' => $this->session->teacher_id);

		$data['schedules'] = $this->users_model->getdata('schedules', $join, null, $where);

		// var_dump($this->session->teacher_id);

		// show_data($data['schedules']); exit;

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/workload', $data);
		$this->load->view('teacher/footer');
	}



	public function attendance($section= null)
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'attendance';


		$join = array(	'section_tb' => 'classes.section_id = section_tb.section_code', 
						'teachers' => 'classes.teacher_id = teachers.teacher_id',
						'users_web' => 'teachers.user_id = users_web.user_id');

		$where = array(	'teachers.user_id' => $this->session->user['user_id']);

		if ($section != null) {
			$where['section_id'] = $section;
		}


		$class = $this->users_model->getdata('classes', $join, null, $where);

		$class_id = $class[0]->class_id;
		$section_code = $class[0]->section_code;


		$join1 = array(	'section_tb' => 'students_tb.stud_section_code = section_tb.section_code');

		$where1 = array(	'stud_section_code' => $section_code);

		$data['students'] = $this->users_model->getdata('students_tb', $join1, null, $where1);
		$data['class_id'] = $class_id;

		// show_data($data); exit;

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/attendance', $data);
		$this->load->view('teacher/footer');
	}

	public function absentall()
	{
        $where = array(
        	'class_id' => $_POST['class_id'], 
        	'class_attendance_date' => $_POST['date']);   

        $data['class_attendance_am'] = null;
        $data['class_attendance_pm'] = null;

    	$query1 = $this->users_model->updatedatani('class_attendance', $where, $data);

        if($query1){
            echo true;
        }
        else{
            echo false;
        }

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









	// public function attendance($class_id, $date = null)
	// {
    //     //e figure out nga dapat and makuha nga students kay mag base sa class id
	// 	$data['page'] = 'attendance';

	// 	if ($date <> null) {			
	// 		$where = array(	'class_attendance.class_id' => $class_id, 'class_attendance_date');
	// 	} else {
	// 		$where = array(	'class_attendance.class_id' => $class_id);	
	// 	}

	// 	$join = array(	'class_attendance' => 'class_attendance.student_id = students_tb.id', 
	// 					'classes' => 'classes.class_id = class_attendance.class_id');

	// 	$data['students'] = $this->users_model->getdata('students_tb', $join, null, $where);

	// 	// show_data($data); exit;

	// 	$this->load->view('teacher/header',$data);
	// 	$this->load->view('teacher/attendance', $data);
	// 	$this->load->view('teacher/footer');
	// }



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

		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/section', $data);
		$this->load->view('teacher/footer');
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



		$where = array(	'class_id' => '', 
						'teacher_id' => '');

		$data['classes'] = $this->users_model->getdata('classes', $join, null, $where);


		$this->load->view('teacher/header',$data);
		$this->load->view('teacher/class', $data);
		$this->load->view('teacher/footer');
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





	public function getlrn() {

		// $where = array( 'student_lrn' => '1', 
		// 				'level' => '1', 
		// 				'section_code' => '1', );

		$where = array( 'student_lrn' => $_POST['lrn'], 
						'level' => $_POST['level'], 
						'section_code' => $_POST['section']);
       
        $query = $this->users_model->getdataid('sf10', $where);

        if ($query) {

	        echo json_encode(array('reply' => true, 'data' => $query));
        } else {
	        echo json_encode(array('reply' => false));

        }


    }


}
