<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Controllers\BaseController;

class CalendarController extends BaseController
{

    // the function getCalendarData() is responsible to update the calendars' event
    // it will runs on cron job to update the user's calendars's events and add new event's 
    //into database table "events"
    // 1 cron

    public function getCalendarData()
    {
        $calobj = model('Calendar');
        $AllCalData = $calobj->findAll();
    
        foreach ($AllCalData as $CalData) {
            $this->getCalDataByRequest($CalData, $CalData);
        }
    }

    public function getCalDataByRequest($caldata, $CalData)
    {
        $client = new Client();
        $uname = $caldata['calcreatedby'];
        $pass = $caldata['calpassword'];
        $username = $uname;
        $password = $pass;

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Set your request headers, including the Authorization header
        $headers = [
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];

        // Define the URL
        $uriLink = $caldata['caldavlink'];
        $url = $uriLink;

        // Create the request
        $request = new Request('PROPFIND', $url, $headers);

        try {
            // Send the request and get the response
            $response = $client->send($request);

            $responseBody = (string) $response->getBody();
            $this->getAndSaveEventDetail($responseBody, $CalData);
        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function getAndSaveEventDetail($responseBody, $CalData)
    {
        $xml = simplexml_load_string($responseBody);

        // Initialize an array to store the event details
        $eventDetails = [];

        foreach ($xml->xpath('//d:response') as $responseNode) {
            $href = (string) $responseNode->xpath('d:href')[0];
            // Check if there are any matching <d:getetag> elements
            $etagElements = $responseNode->xpath('d:propstat/d:prop/d:getetag');

            // Initialize an array to store etag values
            $etags = [];

            // Iterate through etag elements and  add them to the etags array
            foreach ($etagElements as $etagElement) {
                $etags[] = (string) $etagElement;
            }

            // Create an object with the retrieved data
            $event = (object) [
                'href' => $href,
                'etags' => $etags,
            ];

            // Add the object to the eventDetails array
            $eventDetails[] = $event;
        }

        // Output the event details
        foreach ($eventDetails as $event) {
            // Check if the href ends with ".ics"
            if (substr($event->href, -4) === ".ics") {
                $this->sendEventToApi($event->href, $CalData);
            }
        }
    }

    public function sendEventToApi($eventLink, $CalData)
    {

        // Define the API endpoint URL

        $urlParts = parse_url($CalData['caldavlink']);
        $scheme = $urlParts['scheme']; // "https"
        $host = $urlParts['host']; // "baikal.pindot.me"

        // Create the base URL
        $baseUrl = $scheme . '://' . $host;


        $eLink = $baseUrl . $eventLink;

        $client = new Client();
        $uname = $CalData['calcreatedby'];
        $pass = $CalData['calpassword'];
        $username = $uname;
        $password = $pass;

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Set your request headers, including the Authorization header
        $headers = [
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];

        // Make the GET request
        try {
            $response = $client->request('GET', $eLink, [
                'headers' => $headers,
            ]);

            // Get the response body
            $responseData = (string) $response->getBody();


            // Extract the 'UID' field from iCal data
            preg_match('/UID:(.*?)\s/', $responseData, $matches);
            $uid = isset($matches[1]) ? trim($matches[1]) : null;

            // Define variables to store other extracted fields
            $summary = null;
            $description = null;
            $dtstart = null;
            $location = null;

            // Extract other fields if they exist
            preg_match('/SUMMARY:(.*?)\s/', $responseData, $summaryMatches);
            preg_match('/DESCRIPTION:(.*?)\s/', $responseData, $descriptionMatches);
            preg_match('/DTSTART:(.*?)\s/', $responseData, $dtstartMatches);
            preg_match('/LOCATION:(.*?)\s/', $responseData, $locationMatches);

            // Check if other fields were found
            if (isset($summaryMatches[1])) {
                $summary = trim($summaryMatches[1]);
            }
            if (isset($descriptionMatches[1])) {
                $description = trim($descriptionMatches[1]);
            }
            if (isset($dtstartMatches[1])) {
                $dtstart = trim($dtstartMatches[1]);
            }
            if (isset($locationMatches[1])) {
                $location = trim($locationMatches[1]);
            }

            $EventModel = model('EventModel');

            if ($uid !== null) {
                // Check if an event with the same UID exists in the events table for the given calendar
                $existingEvent = $EventModel
                    ->where('event_uid', $uid)
                    ->where('calendar_id', $CalData['id'])
                    ->first();

                if ($existingEvent) {
                    // Update the existing event with new data
                    $existingEvent = model('EventModel');
                    $existingEvent->SUMMARY = $summary;
                    $existingEvent->DESCRIPTION = $description;
                    $existingEvent->DTSTART = $dtstart;
                    $existingEvent->DTEND = $dtstart; // Set DTEND as DTSTART (modify as needed)
                    $existingEvent->LOCATION = '';
                    $existingEvent->calendar_id = $CalData['id'];

                    // Save the updated event
                    $EventModel->update($existingEvent->id, $existingEvent);
                } else {
                    // Create a new event and set its properties
                    $newEvent = [
                        'SUMMARY' => $summary,
                        'DESCRIPTION' => $description,
                        'DTSTART' => $dtstart,
                        'DTEND' => $dtstart,
                        // Set DTEND as DTSTART (modify as needed)
                        'LOCATION' => '',
                        'calendar_id' => $CalData['id'],
                        'event_uid' => $uid,
                        // Set UID
                    ];
                    // Save the new event
                    $EventModel->insert($newEvent);
                    
                }
                echo "Event updated/created successfully.";
            } else {
                // Handle the case where 'UID' is not found in the event data
                echo "UID not found in event data.";
            }

            echo $responseData; // Example: Display the response
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle request exceptions here
            echo 'Error: ' . $e->getMessage();
        }

    }



    // 1 cron Ends here









    public function getUserAllCalendars()
    {
        $client = new Client();
        $username = 'lisa';
        $password = 'admin123';

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Set your request headers, including the Authorization header
        $headers = [
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];

        // Define the URL
        $url = 'http://3.85.127.199/dav.php/calendars/lisa/';

        // Create the request
        $request = new Request('PROPFIND', $url, $headers);

        try {
            // Send the request and get the response
            $response = $client->send($request);

            // Get the response body as XML
            $responseBody = (string) $response->getBody();

            // Parse the XML using SimpleXML
            $xml = simplexml_load_string($responseBody);

            // Initialize an array to store calendar data
            $calendarData = [];

            // Loop through each <d:response> element
            foreach ($xml->xpath('//d:response') as $responseElement) {
                // Extract 'href' and initialize 'displayname' and 'calendar-description'
                $href = (string) $responseElement->xpath('d:href')[0];
                $displayname = '';
                $calendarDescription = '';

                // Check if 'displayname' and 'calendar-description' are present within 'd:prop'
                $propElements = $responseElement->xpath('d:propstat/d:prop');
                if (count($propElements) > 0) {
                    $displayname = (string) $propElements[0]->{'displayname'};
                    $calendarDescription = (string) $propElements[0]->{'calendar-description'};
                }

                // Store the extracted data in the array
                $calendarData[] = [
                    'href' => $href,
                    'displayName' => $displayname,
                    'calendarDescription' => $calendarDescription,
                ];
            }

            // Output the extracted data for each calend ar
            foreach ($calendarData as $calendar) {
                echo "Calendar Href: {$calendar['href']}<br>";
                echo "Display Name: {$calendar['displayName']}<br>";
                echo "Calendar Description: {$calendar['calendarDescription']}<br>";
                echo "<br>";
            }
        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }
    }









    public function createEvent()
    {

        $userId =  $this->request->getPost('userid');
       
        $eventTitle =  $this->request->getPost('eventTitle');
        $fromDate =  $this->request->getPost('fromDate');
        $fromTime =  $this->request->getPost('fromTime');
        $toDate =  $this->request->getPost('toDate');
        $toTime =  $this->request->getPost('toTime');
        $eventDescription = $this->request->getPost('eventDescription');
        
        $calendarModel =model('Calendar'); 
        $eventModel =model('EventModel'); 

        $calendar = $calendarModel
        ->where('user_id', $userId)
        ->where('role', 'booking')
        ->where('status', 'active')
        ->first();

        $calLink=$calendar['caldavlink'];
        $calcreatedby=$calendar['calcreatedby'];
        $calpassword=$calendar['calpassword'];
        $fromDateTime = date('Ymd\THis', strtotime("$fromDate $fromTime"));
        $toDateTime = date('Ymd\THis', strtotime("$toDate $toTime"));
        
        $client = new Client();
        // Define your username and password
        $username =  $calcreatedby;
        $password =  $calpassword;

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Define request headers, including Authorization for Basic Auth
        $headers = [
            'Content-Type' => 'text/calendar; charset=utf-8',
            // Set the Content-Type header
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
        ];

        // Define the request body (your iCalendar data)
        $body = "BEGIN:VCALENDAR\r\n" .
        "VERSION:2.0\r\n" .
        "BEGIN:VEVENT\r\n" .
        "DTSTART:$fromDateTime\r\n" . // Use formatted date and time
        "DTEND:$toDateTime\r\n" .     // Use formatted date and time
        "SUMMARY:$eventTitle\r\n" .   // Use retrieved event title
        "DESCRIPTION:$eventDescription\r\n" . // Use retrieved event description
        "LOCATION:Meeting Room 123\r\n" .
        "END:VEVENT\r\n" .
        "END:VCALENDAR\r\n";
        // Define the URL for the PUT request
        $url = $calLink. $eventTitle.'.ics';
       

        // Create the PUT request
        $request = new Request('PUT', $url, $headers, $body);

        try {
            // Send the PUT request and wait for the response
            $response = $client->send($request);

            // Get and display the response body
            $responseBody = (string) $response->getBody();
            if($responseBody==""){
                $message='Booking Created Successfully';
                return redirect()->to('/');
            }
        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }
    }



    public function mkCalendar()
    {

        $session = session();
        $userModel = model('User');
        $calendarModel = model('Calendar');
        $eventModel = model('EventModel');
        $user_id = (int) session('user_id');
      
        $user_name = session('name');


        // Initialize the Guzzle client
        $client = new Client();

        $calTitle = $this->request->getPost('calTitle');
        $calDescription = $this->request->getPost('calDescription');
        $calendarType1 = $this->request->getPost('calendarType1');
        $calendarType2 = $this->request->getPost('calendarType2');




        // Define your username and password
        $username = 'Lisa';
        $password = 'admin123';

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Define request headers, including Authorization for Basic Auth
        $headers = [
            'Content-Type' => 'application/xml',
            'Cookie' => 'PHPSESSID=k7p58dm0nrlsei2jm2k1gec4ho',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];
 

          
        $randomString = $this->generateRandomString(10);
        $prefix=$user_name;
        $randomString =$prefix.$randomString;
        
        $body = '<?xml version="1.0" encoding="utf-8" ?>
        <C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav" xmlns:ical="http://apple.com/ns/ical/">
           <D:set>
              <D:prop>
                 <D:displayname>' . $randomString . '</D:displayname>
                 <C:calendar-description xml:lang="en">' . $calDescription . '</C:calendar-description>
                 <C:supported-calendar-component-set>
                    <C:comp name="VEVENT"/>
                 </C:supported-calendar-component-set>
                 <C:calendar-timezone><![CDATA[BEGIN:VCALENDAR
        PRODID:-//Example Corp.//CalDAV Client//EN
        VERSION:2.0
        BEGIN:VTIMEZONE
        TZID:US-Eastern
        LAST-MODIFIED:19870101T000000Z
        BEGIN:STANDARD
        DTSTART:19671029T020000
        RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
        TZOFFSETFROM:-0400
        TZOFFSETTO:-0500
        TZNAME:Eastern Standard Time (US & Canada)
        END:STANDARD
        BEGIN:DAYLIGHT
        DTSTART:19870405T020000
        RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
        TZOFFSETFROM:-0500
        TZOFFSETTO:-0400
        TZNAME:Eastern Daylight Time (US & Canada)
        END:DAYLIGHT
        END:VTIMEZONE
        END:VCALENDAR
        ]]></C:calendar-timezone>
                 <ical:calendar-token-id>' . $randomString . '</ical:calendar-token-id>
              </D:prop>
           </D:set>
        </C:mkcalendar>';



        if ($calendarType1 && $calendarType2) {
            $request = new Request('MKCALENDAR', 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString, $headers, $body);
            try {
                // Send the MKCALENDAR request and wait for the response
                $response = $client->send($request);
                // Get and display the response body
                $responseBody = (string) $response->getBody();
                if ($responseBody == "") {
                    $uri = 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString .'/';
                    $user_id = (int) session('user_id');
      
                    $user_name = session('name');
                    $newEvent = [
                        'calname' =>$randomString,
                        'caldescription' =>$calDescription,
                        'caldavlink' =>$uri,
                        'calusername' =>$user_name,
                        // Set DTEND as DTSTART (modify as needed)
                        'calcreatedby' =>$username,
                        'calpassword' =>$password,
                        'user_id' =>$user_id,
                        'role' =>'availability',
                        'status' =>'active',

                        // Set UID
                    ];
                    // Save the new event
                    $calendarModel->insert($newEvent);
                    



                }
            } catch (Exception $e) {
                // Handle any exceptions here
                echo 'Error: ' . $e->getMessage();
            }

            $randomString = $this->generateRandomString(10);
            $prefix=$user_name;
            $randomString =$prefix.$randomString;
            $request = new Request('MKCALENDAR', 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString, $headers, $body);

            try {
                // Send the MKCALENDAR request and wait for the response
                $response = $client->sendAsync($request)->wait();

                // Get and display the response body
                $responseBody = (string) $response->getBody();
                if ($responseBody == "") {
                    $uri = 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString .'/';
                    $user_id = (int) session('user_id');
      
                    $user_name = session('name');
                    $newEvent = [
                        'calname' => $randomString,
                        'caldescription' =>$calDescription,
                        'caldavlink' => $uri,
                        'calusername' => session('name'),
                        // Set DTEND as DTSTART (modify as needed)
                        'calcreatedby' => $username,
                        'calpassword' => $password,
                        'user_id' => $user_id,
                        'role' => 'booking',
                        'status' => 'active',

                        // Set UID
                    ];
                    // Save the new event
                    $calendarModel->insert($newEvent);
                     return redirect()->to('dashboard');
                }
            } catch (Exception $e) {
                // Handle any exceptions here
                echo 'Error: ' . $e->getMessage();
            }

        } elseif ($calendarType1) {

            $randomString = $this->generateRandomString(10);
            $prefix=$user_name;
            $randomString =$prefix.$randomString;
            $request = new Request('MKCALENDAR', 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString, $headers, $body);

            try {
                // Send the MKCALENDAR request and wait for the response
                $response = $client->sendAsync($request)->wait();

                // Get and display the response body
               
                $responseBody = (string) $response->getBody();
                if ($responseBody == "") {
                    $user_id = (int) session('user_id');
      
                    $user_name = session('name');
                    $uri = 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString .'/';
                    $newEvent = [
                        'calname' => $randomString,
                        'caldescription' => $calDescription,
                        'caldavlink' => $uri,
                        'calusername' => session('user_name'),
                        // Set DTEND as DTSTART (modify as needed)
                        'calcreatedby' => $username,
                        'calpassword' => $password,
                        'user_id' => $user_id,
                        'role' => 'booking',
                        'status' => 'active',

                        // Set UID
                    ];
                    // Save the new event
                    $calendarModel->insert($newEvent);
                    return redirect()->to('dashboard');
                    
                }
            } catch (Exception $e) {
                // Handle any exceptions here
                echo 'Error: ' . $e->getMessage();
            }

        } elseif ($calendarType2) {
            $randomString = $this->generateRandomString(10);
            $prefix=$user_name;
            $randomString =$prefix.$randomString;

            $request = new Request('MKCALENDAR', 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString, $headers, $body);

            try {
                // Send the MKCALENDAR request and wait for the response
                $response = $client->sendAsync($request)->wait();

                // Get and display the response body
                $responseBody = (string) $response->getBody();
                $user_id = (int) session('user_id');
      
                $user_name = session('name');
                if ($responseBody == "") {
                    $uri = 'https://baikal.pindot.me/dav.php/calendars/Lisa/' . $randomString.'/';
                    $newEvent = [
                        'calname' => $randomString,
                        'caldescription' => $calDescription,
                        'caldavlink' => $uri,
                        'calusername' => session('user_name'),
                        // Set DTEND as DTSTART (modify as needed)
                        'calcreatedby' => $username,
                        'calpassword' => $password,
                        'user_id' => $user_id,
                        'role' =>'available',
                        'status' =>'active',

                        // Set UID
                    ];
                    // Save the new event
                    $calendarModel->insert($newEvent);
                    return redirect()->to('dashboard');
                }
            } catch (Exception $e) {
                // Handle any exceptions here
                echo 'Error: ' . $e->getMessage();
            }


        } else {


        }
    }















    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function homeCalendar()
    {
        return view('/Calendar/HomeCalendar');
    }





    // Retrieve the availability events of the therapist 
// display to client to create bookings on it

    public function retrieveTherapistAvailibilityEvents($userId)
    {
        $user_id = $userId;

        // Load User_model, Calendar_model, and Event_model
        $userModel = model('User');
        $calendarModel = model(App\Models\Calendar::class);
        $EventModel = model(App\Models\EventModel::class);

        // Retrieve the user with their associated calendars and events
        $user = $userModel->find($user_id);

        // Check if the user exists
        if ($user) {
            // Load the related calendars using a separate query
            $calendarModel = new \App\Models\Calendar();
            $calendars = $calendarModel->where('user_id', $user_id)->findAll();
            $cal_ids = [];
            foreach ($calendars as $calendar) {
                array_push($cal_ids, $calendar['id']);

            }

            $cal_events = [];
            if ($cal_ids) {
                $EventModel = new \App\Models\EventModel();

                foreach ($cal_ids as $cal_id) {
                    $events = $EventModel->where('calendar_id', $cal_id)->findAll();

                    foreach ($events as $event) {
                        $cal_events[] = (object) [
                            'title' => $event['SUMMARY'],
                            'description' => $event['SUMMARY'],
                            'start' => date('Y-m-d\TH:i:s', strtotime($event['DTSTART'])),
                            'end' => date('Y-m-d\TH:i:s', strtotime($event['DTEND'])),
                            'allDay' => false
                        ];
                    }
                }
                // dd($cal_events);
                return view('/Calendar/HomeCalendar', ['eventdata' => $cal_events, 'userId'=>$user_id]);
            } else {
                return redirect('/');
            }

            // Now, $user contains the user data, and $calendars contains their calendars.

            // You can access user properties like $user->name and calendars like $calendars[0]->title.
        } else {
            // Handle the case where the user is not found.
        }
    }



}