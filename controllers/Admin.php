<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

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
        $this->load->model('users_model');
    }

	public function index()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'index';

		$this->load->view('admin/header',$data);
		$this->load->view('admin/index', $data);
		$this->load->view('admin/footer');
	}


	public function user()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'user';


		$select = 'students_tb.* , section_tb.level as level, section_tb.section as section, user_name, user_password';
		$join = array(	'section_tb' => 'section_tb.section_code = students_tb.stud_section_code',
						// 'level_tb' => 'level_tb.level = section_tb.level',
						'users_web' => 'users_web.user_id = students_tb.user_id');
		$where = array(	'students_tb.status' => 1);
		// $where = array(	'users_web.user_status' => 1);


		$data['students'] = $this->users_model->getdata('students_tb', $join, null, $where, $select);
		// $data['students'] = $this->users_model->show('students_tb');
		$data['levels'] = $this->users_model->show('level_tb');

		// show_data($data['students']); exit;

		$this->load->view('admin/header',$data);
		$this->load->view('admin/users', $data);
		$this->load->view('admin/footer');
	}

	public function users()
	{
		$data['page'] = 'users';
		$where1 = array('user_type' => 'admin');
		$array1 = $this->users_model->getdata('users_web', null, null, $where1);

		$where = array('user_type' => 'cashier');
		$array2 = $this->users_model->getdata('users_web', null, null, $where);


		$data['users'] = array_merge($array1, $array2);
		// show_data($data); exit;

		$this->load->view('admin/header',$data);
		$this->load->view('admin/user', $data);
		$this->load->view('admin/footer');
	}



	public function announcement()
	{
		$data['page'] = 'announcement';
		$data['announcement'] = $this->users_model->getdata('announcement');

		// show_data($data); exit;

		$this->load->view('admin/header',$data);
		$this->load->view('admin/announcement', $data);
		$this->load->view('admin/footer');
	}




	public function request()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'request';


		$join = array(	'section_tb' => 'section_tb.section_code = students_tb.stud_section_code',
						'classes' => 'classes.section_id = section_tb.section_code');
		$where = array(	'to_enroll' => 'Yes');


		$select = 'students_tb.* , section_tb.level as level, section_tb.section as section, classes.class_id as class_id';


		$data['students'] = $this->users_model->getdata('students_tb', $join, null, $where, $select);
		// $data['students'] = $this->users_model->show('students_tb');
		$data['levels'] = $this->users_model->show('level_tb');

		// show_data($data['students']); exit;

		$this->load->view('admin/header',$data);
		$this->load->view('admin/request', $data);
		$this->load->view('admin/footer');
	}

	public function teacher()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'teacher';

// 		$data['teachers'] = $this->users_model->getdata('teachers');
		
		
		$join = array(	'users_web' => 'users_web.user_id = teachers.user_id');
		$where = array(	'user_status' => '1');
		$data['teachers'] = $this->users_model->getdata('teachers', $join, null , $where);

		$this->load->view('admin/header',$data);
		$this->load->view('admin/teacher', $data);
		$this->load->view('admin/footer');
	}
	
	

	public function deleteteacher() {

        $id= $_POST['id'];
        $data['user_status'] = 0;
        $query1 = $this->users_model->updatedata('users_web', 'user_id', $id, $data);

        if($query1){

            echo true;
        }
        else{
            echo false;
        }             

    }




	public function section()
	{
        //restrict users to go to home if not logged i
		$data['page'] = 'section';

		$data['sections'] = $this->users_model->getdata('section_tb');
		$data['levels'] = $this->users_model->show('level_tb');
		$data['subjects'] = $this->users_model->show('subject_tb');

		$this->load->view('admin/header',$data);
		$this->load->view('admin/section', $data);
		$this->load->view('admin/footer');
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

		$this->load->view('admin/header',$data);
		$this->load->view('admin/class', $data);
		$this->load->view('admin/footer');
	}

	public function updateclassdata() {
		$this->load->model('users_model'); // Ensure model is loaded

		// Check if POST data is being received
		log_message('debug', 'updateclass: Raw POST data: ' . json_encode($this->input->post()));

		$id = $this->input->post('class_id');
		$class_sy = $this->input->post('class_sy');
		$section_id = $this->input->post('section_id');
		$teacher_id = $this->input->post('teacher_id');

		if (!$id) {
			log_message('error', 'updateclass: class_id is missing.');
			echo json_encode(["error" => "Missing class_id"]);
			return;
		}

		if (empty($class_sy) || empty($section_id) || empty($teacher_id)) {
			log_message('error', 'updateclass: Missing required fields.');
			echo json_encode(["error" => "Missing required fields"]);
			return;
		}

		$data = array(
			'class_sy' => $class_sy,
			'section_id' => $section_id,
			'teacher_id' => $teacher_id
		);

		log_message('debug', "updateclass: Executing update query for class_id: $id with data: " . json_encode($data));

		$query = $this->users_model->updateclass('classes', 'class_id', $id, $data);

		if ($query) {
			log_message('debug', "updateclass: Update successful for class_id: $id");
			echo json_encode(["success" => true]);
		} else {
			log_message('error', "updateclass: Update failed for class_id: $id");
			echo json_encode(["error" => "Update failed"]);
		}
	}


	public function report($level=null, $section=null)
	// public function report($level=null, $section=null)
	{

		$data['page'] = 'report';
		$data['teachers'] = $this->users_model->getdata('teachers');

		$data['section_code'] = $this->users_model->getdata('section_tb');
		$data['levels'] = $this->users_model->getdata('level_tb');


		$join = array(	'rfid_tap_logstb' => 'rfid_tap_logstb.lrn = students_tb.lrn',
						'section_tb' => 'section_tb.section_code = students_tb.stud_section_code');

		$where = array();
		$data['levelby'] = '';
		$data['stud_section_code'] = '';
		$data['dateFrom'] = '';
		$data['dateTo'] = '';

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
		
		$data['students'] = $this->users_model->getdata('students_tb', $join, $order, $where);

		// show_data($data); exit;

		$this->load->view('admin/header',$data);
		$this->load->view('admin/report', $data);
		$this->load->view('admin/footer');
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




	public function updateclass() {


        $id = $_POST['update_class_id'];

        $data['class_sy'] = $_POST['update_class_sy'];
        $data['section_id'] = $_POST['update_section_id'];
        $data['teacher_id'] = $_POST['update_teacher_id'];

        $query = $this->users_model->updatedata('classes', 'class_id', $id, $data);

        if($query){
        	echo true;
        }
        else{
            echo false;
        }   

    }







	public function insertuser() {

        $data['user_name'] = $_POST['user_name'];
        $data['user_password'] = $_POST['user_password'];
        $data['user_type'] = $_POST['user_type'];
        $data['user_status'] = $_POST['user_status'];
       
        $query = $this->users_model->insert('users_web', $data);

        if($query){

            echo true;
        }
        else{
            echo false;
        }

               

    }


	public function insertannouncement() {

        $data['announcement_date'] = $_POST['announcement_date'];
        $data['announcement_title'] = $_POST['announcement_title'];
        $data['announcement_body'] = $_POST['announcement_body'];
        $data['user_id'] = 1;
       
        $query = $this->users_model->insert('announcement', $data);

        if($query){

            echo true;
        }
        else{
            echo false;
        }

               

    }







	public function updateannouncement() {


        $id = $_POST['announcement_id'];
        $data['announcement_date'] = $_POST['update_announcement_date'];
        $data['announcement_title'] = $_POST['update_announcement_title'];
        $data['announcement_body'] = $_POST['update_announcement_body'];

        $query = $this->users_model->updatedata('announcement', 'announcement_id', $id, $data);

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

	        $data1['section_code'] 	= 'SEC' . date('Y') . rand(100000, 999999);
	        $data1['level'] 			= $_POST['level'];
	        $data1['section'] 		= $_POST['section'];
	       
	        $query1 = $this->users_model->insert('section_tb', $data1);


	        if($query1){
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
    
    
	public function insertsubject2() {



		// show_data($_POST); exit;

    	$max = count($_POST['subject']);


    	for ($i=0; $i < $max; $i++) { 
    		for ($y=0; $y < $max; $y++) {
				if( $y != $i) {

					if ($_POST['subject'][$i] == $_POST['subject'][$y]) {
						echo "subject";
						exit;
					}
				} 
    		}
    	}


    	for ($i=0; $i < $max; $i++) { 


			$where2 = array('level' => $_POST['level_id2'], 'subject' => $_POST['subject'][$i]);
			$select = $this->users_model->getdata('subject_tb', NULL, NULL, $where2);

			if ($select) {
				echo 'subject2';
				exit;
			}

		    $data['subject_code'] 	= 'SUB' . date('Y') . rand(100000, 999999);
		    $data['subject'] 		= $_POST['subject'][$i];
		    $data['has_grade'] 		= $_POST['has_grade'][$i];
		    $data['level'] 			= $_POST['level_id2'];

	        $query = $this->users_model->insert('subject_tb', $data);
    	}


	    $data['subject_code'] = 'SUB' . date('Y') . rand(100000, 999999);
	    $data['subject'] 		= $_POST['subject'];
       
        // $query = $this->users_model->insert('subject_tb', $data);

        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }




	public function updatesubject2() {

    	$max = count($_POST['subject3']);

    	for ($i=0; $i < $max; $i++) { 

			$where2 = array('level' => $_POST['level_id3'], 'subject' => $_POST['subject3'][$i]);
			$where2['id !='] = $_POST['subjectid3'][$i];

			$select = $this->users_model->getdata('subject_tb', NULL, NULL, $where2);

			if ($select) {
				echo 'duplicate';
				exit;
			}
    	}

    	for ($i=0; $i < $max; $i++) { 

	        $id = $_POST['subjectid3'][$i];

		    $data['subject'] 		= $_POST['subject3'][$i];
		    $data['has_grade'] 		= $_POST['has_grade3'][$i];
	        $query = $this->users_model->updatedata('subject_tb', 'id', $id, $data);
    	}
 
        if($query){
            echo true;
        }
        else{
            echo false;
        }

    }





	public function getsubject2() {
        $level = $_POST['id'];
        $column = 'level';
        $table = 'subject_tb';

        $where = array($column => $level);
        $query = $this->users_model->getdata($table, null, null, $where);

        echo json_encode($query);

    }

	public function insertschedule() {

		$schedule_start = $_POST['schedule_start'];
		$schedule_end = $_POST['schedule_end'];
		$subject_id = $_POST['subject_id'];
		$class_id = $_POST['class_id'];

		$this->db->select('*')
         ->from('schedules')
         ->where('class_id', $class_id)
         ->where('subject_id', $subject_id);
		$query = $this->db->get();
		$result = $query->result();

		if ($result) {
			echo 'subject';
			exit;
		}


		$this->db->select('*')
         ->from('schedules')
         ->where('class_id', $class_id)
         ->where('schedule_start <', $schedule_start)
         ->where('schedule_end >', $schedule_start);;
		$query1 = $this->db->get();
		$result1 = $query1->result();


		if ($result1) {
			echo 'time';
			exit;
		}



		$this->db->select('*')
         ->from('schedules')
         ->where('class_id', $class_id)
         ->where('schedule_start <', $schedule_end)
         ->where('schedule_end >', $schedule_end);;
		$query2 = $this->db->get();
		$result2 = $query2->result();


		if ($result2) {
			echo 'time';
			exit;
		}

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




	public function insertschedule2() {

		$class_id = $_POST['class_id3'];
    	$max = count($_POST['subjectid3']);

    	//checking sa time
    	
    	for ($i=0; $i < $max; $i++) { 

    		if ($_POST['schedule_start'][$i] > $_POST['schedule_end'][$i]) {    			
				echo 'contime';
				exit;
    		}
    	}


    	for ($i=0; $i < $max; $i++) { 
    		for ($y=0; $y < $max; $y++) { 
	    		if (($_POST['schedule_start'][$i] > $_POST['schedule_start'][$y]) && ($_POST['schedule_start'][$i] < $_POST['schedule_end'][$y])) {    			
					echo 'contime2';
					exit;
	    		}
    		}
    	}


    	
    	// for ($i=0; $i < $max; $i++) { 

		// 	$this->db->select('*')
	    //      ->from('schedules')
	    //      ->where('class_id', $class_id)
	    //      ->where('schedule_start <', $_POST['schedule_start'][$i])
	    //      ->where('schedule_end >', $_POST['schedule_start'][$i]);
		// 	$query1 = $this->db->get();
		// 	$result1 = $query1->result();

		// 	if ($result1) {
		// 		echo 'time';
		// 		exit;
		// 	}

		// 	$this->db->select('*')
	    //      ->from('schedules')
	    //      ->where('class_id', $class_id)
	    //      ->where('schedule_start <', $_POST['schedule_end'][$i])
	    //      ->where('schedule_end >', $_POST['schedule_end'][$i]);;
		// 	$query2 = $this->db->get();
		// 	$result2 = $query2->result();

		// 	if ($result2) {
		// 		echo 'time';
		// 		exit;
		// 	}

    	// }
    	

    	for ($i=0; $i < $max; $i++) { 

    		//check if class and subject exist in schedule
			$where2 = array('class_id' => $class_id, 'subject_id' => $_POST['subjectid3'][$i]);
			$select = $this->users_model->getdata('schedules', NULL, NULL, $where2);

    		//update if exist & add if not
			if ($select) {

			    $data2['teacher_id'] 		= $_POST['teacher_id'][$i];
			    $data2['schedule_start'] 	= $_POST['schedule_start'][$i];
			    $data2['schedule_end'] 		= $_POST['schedule_end'][$i];

		        $query = $this->users_model->updatedata('schedules', 'schedule_id', $_POST['schedule_id'][$i], $data2);


			} else {

			    $data1['subject_id'] 		= $_POST['subjectid3'][$i];
			    $data1['teacher_id'] 		= $_POST['teacher_id'][$i];
			    $data1['schedule_start'] 	= $_POST['schedule_start'][$i];
			    $data1['schedule_end'] 		= $_POST['schedule_end'][$i];
			    $data1['class_id'] 			= $class_id;
		       
		        $query = $this->users_model->insert('schedules', $data1);

			}
    	}

// 		show_data($_POST); exit;
        echo true;

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



	public function getschedules3() {

	    $id		= $_POST['id'];
	    $level		= $_POST['level'];


	    // $id		= 10;
	    // $level		= 1;

		//get sublects by level
        $where = array('level' => $level);
        $query = $this->users_model->getdata('subject_tb', null, null, $where);
        $newdata = [];

        //get teacher and schedule by subject id and level
        foreach ($query as $key => $value) {
			// $join2 = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
			// 				'teachers' => 'schedules.teacher_id = teachers.teacher_id');
			// // $where2 = array('class_id' => 13, 'subject_id' => 17);
			// $where2 = array('class_id' => $id, 'subject_id' => $value->id);
			// $query2 = $this->users_model->getdata('schedules', $join2, null, $where2);


	        $this->db->select('*')
	         ->from('schedules')
	         ->join('subject_tb', 'schedules.subject_id = subject_tb.id', 'left') 
	         ->join('teachers', 'schedules.teacher_id = teachers.teacher_id', 'left') 
	         
	         ->where('subject_id', $value->id)
	         ->where('class_id', $id);
	        $query2 = $this->db->get()->result();
        // $result = $query->result();


			if ($query2) {
				$newdata[] = $query2[0];
			} else {

				$newdata[] = $value;
			}


        }

        echo json_encode($newdata);



        	// show_data($newdata);

		// $join = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id', 
		// 				'teachers' => 'schedules.teacher_id = teachers.teacher_id');

		// $where = array(	$column => $id);

		// $query = $this->users_model->getdata('schedules', $join, null, $where);


    }


	public function insert() {


		// echo "<pre>";
		// echo print_r($_POST);
		// echo "</pre>";
		// exit;


        // $table = $_POST['tableName'];

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
			        $data['gender'] = $_POST['student_gender'];
			        $data['email'] = $_POST['student_email'];
			        $data['picture'] = $imageData;
			        $data['picture2'] = $filename2;
			        
			        $data['birthday'] = $_POST['student_dob'];
			        $data['mobile_no'] = $_POST['student_contact'];
			        // $data['year_id'] = $_POST['year_id'];
			        $data['stud_section_code'] = $_POST['stud_section_code'];
			        $data['notify_via_sms'] = 1;
			        $data['status'] = 1;			        
			        $data['recipient'] = $_POST['student_guardian'];
			        $data['recipient_email'] = $_POST['student_guardian_email'];
			        $data['recipient_mobile_no'] = $_POST['student_guardian_contact'];
			        $data['user_id'] = $userid;
			       
			        $query = $this->users_model->insert('students_tb', $data);

	                if($query){

	                	$this->confirm($userid);

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
			        $data['recipient_email'] = $_POST['student_guardian_email'];
			        $data['recipient_mobile_no'] = $_POST['student_guardian_contact'];
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



	public function updatestudent() {

		// show_data($_POST); exit;


        $student_id = $_POST['student_id'];
        $user_id = $_POST['update_user_id'];


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

		        $user['user_name'] 		= $_POST['update_student_username'];
		        $user['user_password'] 	= $_POST['update_student_password'];

		        $query = $this->users_model->updatedata('users_web', 'user_id', $user_id, $user);

                if($query) {


			        $data['lrn'] = $_POST['update_student_lrn'];
			        $data['firstname'] = $_POST['update_firstname'];
			        $data['middlename'] = $_POST['update_middlename'];
			        $data['lastname'] = $_POST['update_lastname'];
			        $data['address'] = $_POST['update_student_address'];
			        $data['gender'] = $_POST['update_student_gender'];
			        $data['email'] = $_POST['update_student_email'];
			        $data['picture'] = $imageData;
			        $data['picture2'] = $filename2;
			        
			        $data['birthday'] = $_POST['update_student_dob'];
			        $data['mobile_no'] = $_POST['update_student_contact'];
			        $data['stud_section_code'] = $_POST['update_stud_section_code'];
			        $data['notify_via_sms'] = 1;
			        $data['status'] = 1;			        
			        $data['recipient'] = $_POST['update_student_guardian'];
			        $data['recipient_email'] = $_POST['update_student_guardian_email'];
			        $data['recipient_mobile_no'] = $_POST['update_student_guardian_contact'];


			        $query1 = $this->users_model->updatedata('students_tb', 'id', $student_id, $data);


	                if($query1){

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
	        
	        $user['user_name'] 		= $_POST['update_student_username'];
	        $user['user_password'] 	= $_POST['update_student_password'];

	        $query = $this->users_model->updatedata('users_web', 'user_id', $user_id, $user);

            if($query){

			        $data['lrn'] = $_POST['update_student_lrn'];
			        $data['firstname'] = $_POST['update_firstname'];
			        $data['middlename'] = $_POST['update_middlename'];
			        $data['lastname'] = $_POST['update_lastname'];
			        $data['address'] = $_POST['update_student_address'];
			        $data['gender'] = $_POST['update_student_gender'];
			        $data['email'] = $_POST['update_student_email'];
			        
			        $data['birthday'] = $_POST['update_student_dob'];
			        $data['mobile_no'] = $_POST['update_student_contact'];
			        $data['stud_section_code'] = $_POST['update_stud_section_code'];
			        $data['recipient'] = $_POST['update_student_guardian'];
			        $data['recipient_email'] = $_POST['update_student_guardian_email'];
			        $data['recipient_mobile_no'] = $_POST['update_student_guardian_contact'];

		        $query1 = $this->users_model->updatedata('students_tb', 'id', $student_id, $data);


                if($query1){

	                echo true;
                }
                else{
	                echo false;
                }

            }
        }
               

    }




	public function deletestudent() {

        $id= $_POST['id'];
        $data['status'] = 0;
        $query1 = $this->users_model->updatedata('students_tb', 'id', $id, $data);

        if($query1){

            echo true;
        }
        else{
            echo false;
        }

            
        
               

    }




	public function insertlrn() {
		show_data($_POST);



        if(!empty($_FILES['upload']['name'])){
            $config['upload_path'] = './assets/admin/img/';
            //restrict uploads to this mime types
            $config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|txt|xls|xlsx';
            // $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = $_FILES['upload']['name'];
           
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
           
            if($this->upload->do_upload('upload')){

                $uploadData = $this->upload->data();
                // $filename = $uploadData['full_path'];
                // $filename2 = $uploadData['file_name'];


                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];

	            // $imageData = file_get_contents($filename);

	            if (isset($_POST['sf10_id']) && !empty($_POST['sf10_id'])) {


			        $id = $_POST['sf10_id'];
			        $sf10['lrn'] = $filename;
			       
			        $query = $this->users_model->updatedata('sf10', 'sf10_id', $id, $sf10);

			        if ($query) {         
		            	echo false;
			        } else {

		            	echo false;
			        }

	            } else {
	        
			        $sf10['student_lrn'] = $_POST['student_lrn2'];
			        $sf10['level'] = $_POST['level'];
			        $sf10['section_code'] = $_POST['section_code'];
			        $sf10['lrn'] = $filename;
			       
			        $query = $this->users_model->insert('sf10', $sf10);

			        if ($query) {         
		            	echo false;
			        } else {

		            	echo false;
			        }
	            }

            } else {	                
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


	public function updateuser2() {

        $id = $_POST['update_user_id'];
        $user['user_name'] = $_POST['update_user_name'];
        $user['user_password'] = $_POST['update_user_password'];
        $user['user_type'] = $_POST['update_user_type'];
        $user['user_status'] = $_POST['update_user_status'];
       

        $query = $this->users_model->updatedata('users_web', 'user_id', $id, $user);

        if($query){
        	echo true;
        }
        else{
            echo false;
        }   

    }





	public function updatesection() {

        $id = $_POST['student_id'];
        $user['stud_section_code'] = $_POST['section_id'];
        $user['to_enroll'] = '';
       

        $query = $this->users_model->updatedata('students_tb', 'id', $id, $user);

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


    public function getstudentgrades()
    {
        // $id = 75;
        // $class_id = 4;

        $id = $_POST['id'];
        $class_id = $_POST['class_id'];


		$where5 = array('class_id' => $class_id);
		$join4 = array(	'subject_tb' => 'schedules.subject_id = subject_tb.id',
						'teachers' => 'schedules.teacher_id = teachers.teacher_id');
		$order4 = array('schedule_start' => 'asc');
		$schedules = $this->users_model->getdata('schedules', $join4, $order4, $where5);

		$join41 = array(	'schedules' => 'student_grade.schedule_id = schedules.schedule_id',
							'subject_tb' => 'schedules.subject_id = subject_tb.id');

		$student_grade= $this->users_model->getdata('student_grade', $join41, null, array('student_id' => $id));

		$toreturn = array();
		
		foreach ($schedules as $key => $value) {
			$toreturn[$value->schedule_id] = $value;
		}

		foreach ($student_grade as $key => $value) {

			if (array_key_exists($value->schedule_id, $toreturn)) {

				foreach (get_object_vars($value) as $k => $v) {
					$toreturn[$value->schedule_id]->$k = $v;
				}
			}
		}

		echo json_encode($toreturn);
    }








    function send($email, $message){
        // Load PHPMailer library
        $this->load->library('phpmailer_lib');
        
        // PHPMailer object
        $mail = $this->phpmailer_lib->load();
        
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host     = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'montessori.balingasag@gmail.com';
        $mail->Password = 'fehrpotvuokjmshi';
        $mail->SMTPSecure = 'ssl';
        $mail->Port     = 465;
        
        $mail->setFrom('montessori.balingasag@gmail.com', 'Holy Child Montessori');
        
        $mail->addAddress($email);
        
        $mail->Subject = 'Holy Child Montessori';
        
        $mail->isHTML(true);
        
        $mailContent = $message;

        $mail->Body = $mailContent;
        
        // Send email
        if(!$mail->send()){
            // return 'Message could not be sent.';
            // return 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
            
        }else{
            return false;
            // retun 'Message has been sent';
        }
    }



    function confirm($user_id) {


		$join = array(	'users_web' => 'users_web.user_id = students_tb.user_id');
		$where = array(	'students_tb.user_id' => $user_id);

		$student = $this->users_model->getdata('students_tb', $join, null, $where);

        $mailContent = "<h3>Congratulations you are now officially enrolled. Below are your credential details:</h3><br>";

        $mailContent .= "<h3>Username: ". $student[0]->user_name."</h3>";
        $mailContent .= "<h3>Password: ". $student[0]->user_password."</h3><br>";

        // send($ticket['contact_email'], $mailContent);
        $this->send($student[0]->email, $mailContent);

    }



}
