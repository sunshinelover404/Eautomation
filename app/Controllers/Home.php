<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $session = session();
        $userModel = model('User');
        $calendarModel = model('Calendar');
        $user_id = session('user_id');
        $userModel = model('User');
        $users = $userModel->findAll();
        return view('welcome_user', ['users' => $users]);
    }
}
