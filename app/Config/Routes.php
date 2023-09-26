<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

 // User Authentication 
$routes->get('/', 'Home::index');
$routes->get('/therapist/register', 'UserController::register');
$routes->post('/register', 'UserController::do_register');
$routes->get('/therapist/login', 'UserController::login');
$routes->post('/login', 'UserController::do_login');
$routes->get('/dashboard', 'UserController::dashboard'); //['filter' => 'auth']
$routes->get('logout', 'UserController::logout');

// User Authentication Exit  






// Calendar routes
$routes->get('/getCalendarData','CalendarController::getCalendarData');
$routes->get('/getUserAllCalendars','CalendarController::getUserAllCalendars');
$routes->post('/createevent','CalendarController::createEvent');
$routes->post('/mkCalendar','CalendarController::mkCalendar');
$routes->get('/homecalendar','CalendarController::homeCalendar');
$routes->get('/retrieveTherapistAvailibilityEvents/(:num)','CalendarController::retrieveTherapistAvailibilityEvents/$1');
