<?php

namespace App\Controllers;
use App\Models\User;
use App\Controllers\BaseController;

class UserController extends BaseController
{
   

    public function register()
    {
        $userModel = model('User');
    
        return view('register_view');
    }
    public function login()
    {
        $userModel = model('User');
    
        return view('login');
    }
    public function dashboard()
    {
        $userModel = model('User');
    
        return view('dashboard');
    }

    // Function to handle user registration form submission
    public function do_register()
    {
        $userModel = model('User');
        // return $this->response->setJSON('hello');
        
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            // Add other user information as needed
        ];
    
        // Create a validation object
        $validation = \Config\Services::validation();
    
        // Set validation rules
        $validation->setRules($userModel->validationRules);
    
        try {
            // Validate the input
            if ($validation->withRequest($this->request)->run() === false) {
                // Validation failed, throw a ValidationException
                throw ValidationException::withMessages($validation->getErrors());
            }
    
            // Validation successful, attempt to save the user data
            if ($userModel->save($data) === false) {
                // Saving failed, throw an Exception
                throw new \Exception('User registration failed');
            }
    
            // Registration successful
            $response = ['message' => 'Registration successful'];
        } catch (\Exception $e) {
            // Handle the validation or saving exception
            $response = ['message' => $e->getMessage()];
        }
    
        // Return the response as JSON
        return $this->response->setJSON($response);
    }

    

    public function do_login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = model('User');
        $session = session();
      
        $user = $userModel->get_user_by_email($email);
        //dd($user);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $user_data = [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'logged_in' => true
                ];

                $session->set($user_data);
           
                $user_id = $session->get('user_id');
                // $email = $session->get('email');
                // $logged_in = $session->get('logged_in');
                
                // // Halt the execution and display the session data
                // die("User ID: $user_id, Email: $email, Logged In: $logged_in");

                return redirect()->to('dashboard'); // Redirect to the dashboard upon successful login
            } else {
                return redirect()->to('login')->with('error', 'Invalid credentials');
            }
        } else {
            return redirect()->to('login')->with('error', 'User not found');
        }
    }


    public function logout()
{
    $session = session();
    $session->destroy(); // Destroy the user's session data
    return redirect()->to('login'); // Redirect to the login page after logout
}
    
}
