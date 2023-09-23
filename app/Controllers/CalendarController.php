<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Controllers\BaseController;

class CalendarController extends BaseController
{
    public function getCalendarData()
    {

        $calobj = model('Calendar');
        $AllCalData = $calobj->findAll();
        foreach ($AllCalData as $CalData) {
            $this->getCalData($CalData);

        }
    }



    public function getCalData($caldata)
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

            $this->getAndSaveEventDetail($responseBody);


        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }

    }


    public function getAndSaveEventDetail($responseBody)
    {
        $xml = simplexml_load_string($responseBody);
        // Initialize an array to store the event details
        foreach ($xml->xpath('//d:response') as $responseNode) {
            $href = (string) $responseNode->xpath('d:href')[0];

            // Check if there are any matching <d:getetag> elements
            $etagElements = $responseNode->xpath('d:propstat/d:prop/d:getetag');

            // Initialize a n array to store etag values
            $etags = [];

            // Iterate through etag elements and add them to the etags array
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
        $this->retrievingSavingSingleEvent($event);
           

        }
 
        // Save or process the event details as needed
        // For example, you can save them to a database, perform calculations, or output them
        // Here, we'll simply output the event details using dd()


    }





    public function retrievingSavingSingleEvent($event){
       
    }






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

            // Output the extracted data for each calendar
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
        // Initialize the Guzzle client
        $client = new Client();

        // Define your username and password
        $username = 'Lisa';
        $password = 'admin123';

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
            "DTSTART:20230917T100000\r\n" .
            "DTEND:20230917T120000\r\n" .
            "SUMMARY:LisaTestEvent\r\n" .
            "DESCRIPTION:This is a sample event created via CalDAV.\r\n" .
            "LOCATION:Meeting Room 123\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n";

        // Define the URL for the PUT request
        $url = 'https://baikal.pindot.me/dav.php/calendars/Lisa/default/LisaTestEvent.ics';

        // Create the PUT request
        $request = new Request('PUT', $url, $headers, $body);

        try {
            // Send the PUT request and wait for the response
            $response = $client->send($request);

            // Get and display the response body
            $responseBody = (string) $response->getBody();
            echo $responseBody;
        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }
    }



    public function mkCalendar()
    {
        // Initialize the Guzzle client
        $client = new Client();

        // Define your username and password
        $username = 'lisa';
        $password = 'admin123';

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Define request headers, including Authorization for Basic Auth
        $headers = [
            'Content-Type' => 'application/xml',
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];

        // Define the XML body for the MKCALENDAR request
        $xmlBody = '<?xml version="1.0" encoding="utf-8" ?>
        <C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav" xmlns:ical="http://apple.com/ns/ical/">
            <D:set>
                <D:prop>
                    <D:displayname>LisaEve111111</D:displayname>
                    <C:calendar-description xml:lang="en">Calendar restricted to events.</C:calendar-description>
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
                    <ical:calendar-token-id>lisacal</ical:calendar-token-id>
                </D:prop>
            </D:set>
        </C:mkcalendar>';

        // Define the URL for the MKCALENDAR request
        $url = 'http://3.85.127.199/dav.php/calendars/lisa/LisaEve111111';

        // Create the MKCALENDAR request
        $request = new Request('MKCALENDAR', $url, $headers, $xmlBody);

        try {
            // Send the MKCALENDAR request and wait for the response
            $response = $client->send($request);

            // Get and display the response body
            $responseBody = (string) $response->getBody();
            echo $responseBody;
        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }
    }



    public function homeCalendar()
    {
        return view('/Calendar/HomeCalendar');
    }







    public function retrieveEvents()
    {
        $user_id = 1;

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
                return view('/Calendar/HomeCalendar', ['eventdata' => $cal_events]);
            } else {
                // Handle the case where $cal_ids is empty or no events are found.
            }

            // Now, $user contains the user data, and $calendars contains their calendars.

            // You can access user properties like $user->name and calendars like $calendars[0]->title.
        } else {
            // Handle the case where the user is not found.
        }
    }



}