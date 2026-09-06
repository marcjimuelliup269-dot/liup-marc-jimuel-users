<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentController extends Controller {

    public function index() {
        // Load student home view
        $this->call->view('student_home');
    }

    public function profile() {
        // Sample student data
        $student = [
            'student_id' => '2006-01-20',
            'name'       => 'Marc Jimuel Liup',
            'course'     => 'BS Information Technology',
            'year'       => '3rd Year',
            'section'    => '3-F5',
            'email'      => 'marcjimuelliup269@gmail.com'
        ];
        // Pass data to view
        $this->call->view('student_profile', $student);
    }
}
