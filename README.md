Environmental Impact Assessment Scraper and Notifier
============
EIA project scraper, processor and notifier for Slovakia.

Features
------------
* Scrapes latest EIA projects
* Generates prefilled documents and uploads them to Google Drive on-the-fly
* Sends notifications with the custom filtered results to an email

Future plans
------------
* Automatic sending of the generated documents (from template), if no action taken until deadline by email and snail mail.

Dependencies
------------

### HTML pages parsing
PHP Simple HTML DOM Parser: http://simplehtmldom.sourceforge.net

### Email
PHPMail: https://github.com/PHPMailer/PHPMailer

### Template processing and document generation
PHPWord: https://github.com/PHPOffice/PHPWord

### Google Drive access
Google APIs Client Library for PHP: https://github.com/google/google-api-php-client