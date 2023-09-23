<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

 // User Authentication 
$routes->get('/', 'Home::index');
$routes->get('/register', 'UserController::register');
$routes->post('/register', 'UserController::do_register');
$routes->get('/login', 'UserController::login');
$routes->post('/login', 'UserController::do_login');
$routes->get('/dashboard', 'UserController::dashboard', ['filter' => 'auth']);
$routes->get('logout', 'UserController::logout');

// User Authentication Exit  






// Calendar routes
$routes->get('/getCalendarData','CalendarController::getCalendarData');
$routes->get('/getUserAllCalendars','CalendarController::getUserAllCalendars');
$routes->get('/createevent','CalendarController::createEvent');
$routes->get('/mkcalendar','CalendarController::mkCalendar');
$routes->get('/homecalendar','CalendarController::homeCalendar');
$routes->get('/retrieveEvents','CalendarController::retrieveEvents');
