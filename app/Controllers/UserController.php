<?php

namespace App\Controllers;
use App\Models\User;
use App\Controllers\BaseController;

class UserController extends BaseController
{
   

    public function register()
    {
       // $userModel = model('User');
    
        return view('TherapistView/register');
    }
    public function login()
    {
        $userModel = model('User');
    
        return view('TherapistView/login');
    }








  


public function dashboard()
{
    $session = session();
    $userModel = model('User');
    $calendarModel = model('Calendar');
    $user_id = session('user_id');
 
    // Retrieve calendar data for the user
    $calendarData = $calendarModel->where('user_id', $user_id )->findAll();
    


    // Check if any records were found
    // Check if any records were foundt
    $recordCount = count($calendarData);
 
    if (count($calendarData) > 0) {
        // Records found, set $record to true
        $record = true;
    } else {
        // No records found, set $record to false
        $record = false;
    }

    // Pass the data to the view
    return view('TherapistView/dashboard', [
        'calendarData' => $calendarData,
        'record' => $record,
        'recordcount'=> $recordCount
    ]);
}










    // Function to handle user registration form submission
    public function do_register()
    {
        $userModel = model('User');
        // return $this->response->setJSON('hello');
        
        $data = [
            'name' => $this->request->getPost('name'),
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
                    'name' => $user['name'],
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
                return redirect()->to('/therapist/login')->with('error', 'Invalid credentials');
            }
        } else {
            return redirect()->to('/therapist/login')->with('error', 'User not found');
        }
    }


    public function logout()
{
    $session = session();
    $session->destroy(); // Destroy the user's session data
    return redirect()->to('/therapist/login'); // Redirect to the login page after logout
}
    
}
