<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
	public function index()
	{

$date = DateTime::createFromFormat('Y-m', "2024-03");
$year = $date->format('Y');
$month = $date->format('m');

		echo "<pre>";
		echo print_r($year);
		echo print_r($month);
		echo "</pre>";
		exit;
	}



	public function test()
	{

		$test = (int)date('Y');
		$test2 = (int)date('Y') + 1;
		// $test = date('Y');

		echo "<pre>";
		echo var_dump($test);
		echo print_r($test2);
		echo "</pre>";
		exit;
	}

}
