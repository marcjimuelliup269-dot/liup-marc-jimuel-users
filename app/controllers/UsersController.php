<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UsersController extends Controller
{
    public function index()
    {
        $this->call->database();
        $this->UsersModel = $this->call->model('UsersModel');
        $users = $this->UsersModel->all();

        $this->call->view('users', ['users' => $users]);
    }
}