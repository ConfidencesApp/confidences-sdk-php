# Confidences PHP SDK

[![Build Status](https://travis-ci.org/ConfidencesApp/confidences-sdk-php.svg?branch=master)](https://travis-ci.org/ConfidencesApp/confidences-sdk-php)
[![Latest Stable Version](https://poser.pugx.org/ConfidencesApp/confidences-sdk-php/v/stable)](https://packagist.org/packages/ConfidencesApp/confidences-sdk-php)
[![License](https://poser.pugx.org/ConfidencesApp/confidences-sdk-php/license.svg)](https://packagist.org/packages/ConfidencesApp/confidences-sdk-php)
[![Code Coverage](https://coveralls.io/repos/ConfidencesApp/confidences-sdk-php/badge.svg?branch=master)](https://coveralls.io/r/ConfidencesApp/confidences-sdk-php?branch=master)

You can sign up for a Confidences account at https://confidences.co.

## Requirements

PHP 5.6.x and later.

## Composer

You can install the SDK via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require confidencesapp/confidences-sdk-php
```

To use the SDK, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/confidencesapp/confidences-sdk-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/confidences-sdk-php/init.php');
```

## Dependencies

The SDK require the following extension in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)
- [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php) (Multibyte String)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

Simple usage looks like:

```php
\Confidences\Confidences::setApiKey('4gJam3Mrx0B5NwgYSchf0IMqTW4h5x84');
$result = \Confidences\Survey::share('campaignToken', 'emailAddressOrMobilePhone', ['custom_var_1' => 'Custom value 1', 'custom_var_n' => 'Custom value N']);
if($result) {
	echo 'successfully shared !';
} else {
	echo 'sharing error : ' . \Confidences\Survey::getException();
}
```

## Custom Request Timeouts

*NOTE:* We do not recommend decreasing the timeout for non-read-only calls (e.g. survey share), since even if you locally timeout, the request on Confidences's side can still complete.

To modify request timeouts (connect or total, in seconds) you'll need to tell the API client to use a CurlClient other than its default. You'll set the timeouts in that CurlClient.

```php
// set up your tweaked Curl client
$curl = new \Confidences\HttpClient\CurlClient();
$curl->setTimeout(10); // default is \Confidences\HttpClient\CurlClient::DEFAULT_TIMEOUT
$curl->setConnectTimeout(5); // default is \Confidences\HttpClient\CurlClient::DEFAULT_CONNECT_TIMEOUT

echo $curl->getTimeout(); // 10
echo $curl->getConnectTimeout(); // 5

// tell Confidences to use the tweaked client
\Confidences\Api\ApiRequestor::setHttpClient($curl);

// use the Confidences API client as you normally would
```

## Custom cURL Options (e.g. proxies)

Need to set a proxy for your requests? Pass in the requisite `CURLOPT_*` array to the CurlClient constructor, using the same syntax as `curl_stopt_array()`. This will set the default cURL options for each HTTP request made by the SDK, though many more common options (e.g. timeouts; see above on how to set those) will be overridden by the client even if set here.

```php
// set up your tweaked Curl client
$curl = new \Confidences\HttpClient\CurlClient(array(CURLOPT_PROXY => 'proxy.local:80'));
// tell Confidences to use the tweaked client
\Confidences\Api\ApiRequestor::setHttpClient($curl);
```

Alternately, a callable can be passed to the CurlClient constructor that returns the above array based on request inputs. See `testDefaultOptions()` in `tests/CurlClientTest.php` for an example of this behavior. Note that the callable is called at the beginning of every API request, before the request is sent.

### SSL / TLS compatibility issues

Confidences's API allows all connections use TLS 1.0, 1.1 and 1.2. We strongly recommend switching to TLS 1.2 on your servers.

You might be able to change the TLS version by setting the `CURLOPT_SSLVERSION` option to either `CURL_SSLVERSION_DEFAULT` or `CURL_SSLVERSION_TLSv1` or `CURL_SSLVERSION_TLSv1_2`:

```php
$curl = new \Confidences\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
\Confidences\Api\ApiRequestor::setHttpClient($curl);
```

## Development

Install dependencies:

``` bash
composer install
```

## Tests

Install dependencies as mentioned above (which will resolve [PHPUnit](http://packagist.org/packages/phpunit/phpunit)), then you can run the test suite:

```bash
./vendor/bin/phpunit
```

Or to run an individual test file:

```bash
./vendor/bin/phpunit tests/ConfidencesTest/SurveyTest
```

### SSL / TLS configuration option

See the "SSL / TLS compatibility issues" paragraph above for full context. If you want to ensure that your plugin can be used on all systems, you should add a configuration option to let your users choose between different values for `CURLOPT_SSLVERSION`: none (default), `CURL_SSLVERSION_TLSv1` and `CURL_SSLVERSION_TLSv1_2`.
