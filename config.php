<?php
// Authentication credentials
define('AUTH_USER', 'yourUsername');
define('AUTH_PASS', 'yourPassword');

// URLs of the calendars to be merged
$calendar_urls = [
    'https://example.com/calendar1.ics',
    'https://example.com/calendar2.ics'
];

// Optional: Set the name of the calendar
$calendar_name = 'My Merged Calendar';

// Optional: Set the colour of the calendar (Hex colour in RGB, e.g. #FF0000 for Red)
$calendar_colour = '#FF0000'; // Red

// Optional: cache the merged calender to a file, we'll send this file instead of a new merge if it is not older than 30 minutes
$cachefile = ''; //e.g. /tmp/cache.ics
?>
