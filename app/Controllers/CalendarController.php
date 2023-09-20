<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Controllers\BaseController;

class CalendarController extends BaseController
{
    public function getCalendarData()
    {


        $client = new Client();
        $username = 'adnan';
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
        $url = 'http://3.85.127.199/dav.php/calendars/adnan/default';

        // Create the request
        $request = new Request('PROPFIND', $url, $headers);

        try {
            // Send the request and get the response
            $response = $client->send($request);
            $responseBody = (string) $response->getBody();
            $xml = simplexml_load_string($responseBody);

            // Extract 'displayname' and 'calendar-description'
            $displayname = (string) $xml->xpath('//d:displayname')[0];
            $calendarDescription = (string) $xml->xpath('//cal:calendar-description')[0];
            $href = (string) $xml->xpath('//d:href')[0];
            $timezone = (string) $xml->xpath('//cal:calendar-timezone')[0];
            $eventDetails = [];

            foreach ($xml->xpath('//d:response') as $responseElement) {
                $href = (string) $responseElement->xpath('d:href')[0];
                $lastModifiedElement = $responseElement->xpath('d:propstat/d:prop/d:getlastmodified');
                $etagElement = $responseElement->xpath('d:propstat/d:prop/d:getetag');

                $lastModified = $lastModifiedElement ? (string) $lastModifiedElement[0] : 'N/A';
                $etag = $etagElement ? (string) $etagElement[0] : 'N/A';

                $eventDetails[] = [
                    'eventLink' => $href,
                    'lastModified' => $lastModified,
                    'etag' => $etag,
                ];
            }

            // Output the event details
            foreach ($eventDetails as $event) {
                echo "Event Link: {$event['eventLink']}<br>";
                echo "Last Modified: {$event['lastModified']}<br>";
                echo "ETag: {$event['etag']}<br>";
                echo "<br>";
            }

        } catch (Exception $e) {
            // Handle any exceptions here
            echo 'Error: ' . $e->getMessage();
        }

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
        $username = 'lisa';
        $password = 'admin123';

        // Create a base64-encoded Basic Authentication string
        $authHeader = 'Basic ' . base64_encode("$username:$password");

        // Define request headers, including Authorization for Basic Auth
        $headers = [
            'Content-Type' => 'text/plain',
            'Cookie' => 'PHPSESSID=v8vdteqron2ltu8oqino1ba7q8',
            'Authorization' => $authHeader,
            // Add the Authorization header
        ];

        // Define the request body (your iCalendar data)
        $body = 'BEGIN:VCALENDAR
        VERSION:2.0
        BEGIN:VEVENT
        DTSTART:20230917T100000
        DTEND:20230917T120000
        SUMMARY:LisaTestEvent
        DESCRIPTION:This is a sample event created via CalDAV.
        LOCATION:Meeting Room 123
        END:VEVENT
        END:VCALENDAR';

        // Define the URL for the PUT request
        $url = 'http://3.85.127.199/dav.php/calendars/lisa/default/LisaTestEvent.ics';

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



    public function mkCalendar() {
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
            'Authorization' => $authHeader, // Add the Authorization header
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



}