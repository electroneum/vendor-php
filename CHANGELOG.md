# Changelog

All released changes will be documented in this file.

## [Unreleased]

## [0.1.1] - 2018-10-01
* Fixed payload order in `/example/poll-confirmation.php`
* Updated `URL_SUPPLY` to use cUrl (preferred over `file_get_contents()`) and throw exception if neither are available - reported and recommend fix by [Benjaminoo](https://community.electroneum.com/t/proposed-workaround-for-php-servers-that-have-disabled-allow-url-fopen/5517)
* Remote API updates include:
  * Poll confirmation signature is now case insensitive
  * Webhook response now includes an `event` parameter
  * Poll http response updated to `200` from `400` on `status: 0`

## [0.1.0] - 2018-09-10
* First beta release of the Electroneum Vendor PHP API
