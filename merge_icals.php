<?php
// Force HTTPS for security
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    die('Verbindung unsicher. HTTPS erforderlich.');
}

// Include the configuration file
include 'config.php'; // <-- Adding this line to include config.php

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Function to log failed login attempts with IP address
function log_failed_login(): void {
    $fileid = basename(__FILE__);
    $ip = $_SERVER['REMOTE_ADDR'];  // Get the IP address of the user
    $log_message = "$fileid: Failed login attempt from IP: $ip";
    error_log($log_message);  // Log the message to the PHP error log
}

// Function to perform Basic Authentication
function check_auth(): void {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || 
        $_SERVER['PHP_AUTH_USER'] != AUTH_USER || $_SERVER['PHP_AUTH_PW'] != AUTH_PASS) {
        
        log_failed_login();  // Log failed login attempt
        
        header('WWW-Authenticate: Basic realm="Calendar Access"');
        header('HTTP/1.0 401 Unauthorized');
        die('Access denied. Incorrect username or password.');
    }
}

// Check Basic Authentication (you can disable this if not needed)
check_auth();

// Function to log iCalendar retrieval errors
function log_calendar_error($url) {
    $fileid = basename(__FILE__); 
    $log_message = "$fileid: Failed to retrieve iCalendar from $url";
    error_log($log_message);  // Log the message to the PHP error log
}

// Function to fetch an iCalendar via HTTPS
function fetch_calendar($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Optional: in case of self-signed certificates
    curl_setopt($ch, CURLOPT_HEADER  , true);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpcode != 200) {
        log_calendar_error($url);  // Log the error if fetching fails
    }

    curl_close($ch);
    
    return $data;
}

// Function to parse an iCalendar content into events
function parse_ical($ical_content) {
    $lines = explode("\n", $ical_content);
    $events = [];
    $event = null;

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line == "BEGIN:VEVENT") {
            $event = [];
        } elseif ($line == "END:VEVENT") {
            $events[] = $event;
            $event = null;
        } elseif ($event !== null) {
            list($key, $value) = explode(":", $line, 2);
            $event[$key] = $value;
        }
    }

    return $events;
}

// Function to merge multiple calendars
function merge_calendars($calendar_urls) {
    $merged_events = [];

    foreach ($calendar_urls as $url) {
        $calendar_content = fetch_calendar($url);
        if ($calendar_content) {
            $events = parse_ical($calendar_content);
            $merged_events = array_merge($merged_events, $events);
        }
    }

    return $merged_events;
}

// Function to generate an iCalendar content from the merged events
function generate_ical($events, $calendar_name = null, $calendar_colour = null) {
    $output = "BEGIN:VCALENDAR\r\n";
    $output .= "VERSION:2.0\r\n";
    $output .= "PRODID:-//Your Company//Your Product//EN\r\n";
    
    // Add calendar name (optional)
    if ($calendar_name) {
        $output .= "X-WR-CALNAME:$calendar_name\r\n";
    }

    // Add calendar colour (optional, supported by Apple Calendar)
    if ($calendar_colour) {
        $output .= "X-APPLE-CALENDAR-COLOR:$calendar_colour\r\n";
    }

    foreach ($events as $event) {
        $output .= "BEGIN:VEVENT\r\n";
        foreach ($event as $key => $value) {
            $output .= "$key:$value\r\n";
        }
        $output .= "END:VEVENT\r\n";
    }

    $output .= "END:VCALENDAR\r\n";
    
    return $output;
}

// Main part of the script
// Merge the calendars
$merged_events = merge_calendars($calendar_urls);

// Generate the iCalendar output with name and colour
$ical_content = generate_ical($merged_events, $calendar_name, $calendar_colour);

// Output the iCalendar file as a download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename="merged_calendar.ics"');

echo $ical_content;

?>
