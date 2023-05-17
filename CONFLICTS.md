# CONFLICTS

This document explains why certain conflicts were added to `composer.json` and references related issues.

 - `php-http/message:1.16.0`:

   This version is causing a problem:
   `You cannot use "Http\Message\MessageFactory\GuzzleMessageFactory" as the "php-http/message-factory" package is not 
    installed. Try running "composer require php-http/message-factory". Note that this package is deprecated, 
    use "psr/http-factory" instead.`
