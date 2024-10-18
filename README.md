# php-icalendar-merge
PHP Script to download multiple iCalendar abonnements and merge them into one

## basic auth password protection
Comment out line ```check_auth();``` for disabling basic auth.
```php
// Username and password for access
define('AUTH_USER', 'yourUsername');
define('AUTH_PASS', 'yourPassword');
```

## iCalendar imputs
```php
$calendar_urls = [
    'https://example.com/calendar1.ics',
    'https://example.com/calendar2.ics'
];
```

## name of the calendar
```php
$calendar_name = 'My Merged Calendar';
```

## colour of the calendar
Hex colour in RGB, e.g. #FF0000 for Red
```php
$calendar_colour = '#FF0000'; // Red
```
