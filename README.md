# Electroneum Vendor PHP API

Our Instant Payment Vendor API allows users to act as vendors and
receive instant notification of a payment.

This allows you to accept ETN as instant payment through online
e-commerce, retail ePOS (electronic point-of-sale) or our mobile app
with assurance that a payment is on its way.

An instant notification will be created on your account, accessible via
the mobile app, allowing you to receive confirmation that a payment is
on its way.

Using HTTP RESTful features, our API can optionally send a signed
webhook when a payment is made to you. You authenticate this by
confirming the signature of the webhook.

You can sign up for an Electroneum user account at
[my.electroneum.com/register](https://my.electroneum.com/register) and
apply for a vendor account at
[my.electroneum.com/user/vendor](https://my.electroneum.com/user/vendor).

Let us know when your integration is live, we’d love to share the news!
You can share images & videos on
[Twitter](https://twitter.com/electroneum),
[Facebook](https://www.facebook.com/electroneum) or on the
[community forum](https://community.electroneum.com/c/api-developers).

## Status

This API is currently in BETA phase. During this phase, we accept no
responsibility for any false notifications nor affirm the identity of
any vendors.

There is a maximum spend limit of €500.00 per transaction; this figure
is converted to ETN at a regular interval based on market data. Please
do not try to present a QR code to users above this limit as the
transaction will fail.

The `composer` version should used should be `beta` during this phase.

## Changelog

All released changes will be documented in this section.

### 2019-03-22
- Added more currencies: ARS, BDT, COP, EGP, GHS, NGN, RON, UAH, VES, VND
- Removed VERSION.md and moved CHANGELOG.md into README.md

### 2018-10-01
- Fixed payload order in `/example/poll-confirmation.php`
- Updated `URL_SUPPLY` to use cUrl (preferred over `file_get_contents()`) and throw exception if neither are available - reported and recommend fix by [Benjaminoo](https://community.electroneum.com/t/proposed-workaround-for-php-servers-that-have-disabled-allow-url-fopen/5517)
- Remote API updates include:
  - Poll confirmation signature is now case insensitive
  - Webhook response now includes an `event` parameter
  - Poll http response updated to `200` from `400` on `status: 0`

### 2018-09-10
- First beta release of the Electroneum Vendor PHP API

## Support

Community support is available at
[community.electroneum.com](https://community.electroneum.com/c/api-developers).

## Documentation

Further documentation on data structures (useful to create your own
integration or to create the QR code yourself) and our Vendor API can
be found at [community.electroneum.com](https://community.electroneum.com/t/using-the-etn-instant-payment-api/121).

## Requirements

The following are required for using the Vendor API:
- Electroneum user account with Vendor API enabled
- PHP v5.4.0 or later with the extensions:
  - ext-ctype
  - ext-curl (or enable `allow_url_fopen`)
  - ext-json

## Download

You can download the latest PHP API from
[github.com/electroneum/vendor-php](https://github.com/electroneum/vendor-php).

## Installation

### Manual Installation

Unpack the API and include the Electroneum vendor class:

```php
require_once('src/vendor.php');
```

### Composer Installation

Using composer, you can easily install with:

```
composer require electroneum/vendor-php dev-beta
```

Alternatively, you can add the following to your `composer.json`:

```
"require": {
    "electroneum/vendor-php": "dev-beta"
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/electroneum/vendor-php"
    }
],
```

## Quick Use

This is a quick guide on using our Vendor API with PHP.

Generate a QR code for the customer:

```php
// Create the Vendor object.
$vendor = new \Electroneum\Vendor\vendor(
    'key_live_1234567890abcdefghijklm',
    'sec_live_zxyxwvutsrqponmlkjihgfedcba0987654321zxyxwvutsrqponmlkj'
);

// Create a QR code, passing the amount to charge, currency & outlet id.
$qrImgUrl = $vendor->getQr(9.95, 'GBP', '0abc123def456');
$paymentId = $vendor->getPaymentId();
```

Listen for a webhook confirming the payment:

```php
// Get the payload and signature from an incoming webhook.
$payload = @file_get_contents('php://input');
$signature = @$_SERVER['HTTP_ETN_SIGNATURE'];

// Verify the signature.
if ($vendor->verifySignature($payload, $signature)) {
    http_response_code(200);

    // Process the transaction.
    $payload = json_decode($payload);
    ...
} else {
    http_response_code(401);
}
```

## Getting Started

Simply create a `Vendor()` object. You don't need to pass any variables
unless you are verifying a webhook signature or polling for payment
confirmation (see below):

```php
$vendor = new \Electroneum\Vendor\vendor();
```

### Create a QR Code

To accept a vendor instant payment notification, you present a QR code
with the necessary information for your customer to scan using the
Electroneum app on their mobile device.

You can create this using the `getQr($amount, $currency, $outlet,
$paymentId = null)` method of the Vendor object with the following
parameters:
- `$amount`<br />The amount to charge in your local currency (accepted
  currencies available below).
- `$currency`<br />Your local currency code (from the list below).
- `$outlet`<br />The id of the outlet from your user vendor account,
  available from your [user vendor account](https://my.electroneum.com/user/vendor).
- `$paymentId`<br />The unique identifier of this transaction. If not
  provided, this will be automatically generated.

The `getQr()` method will return a [Google Chart](https://developers.google.com/chart/infographics/docs/qr_codes)
URL of the QR code image.

The `$currency` must be one of the following three-digit codes:

| Name                       | Code | JSON Key  |
|----------------------------|------|-----------|
| Argentina Peso             | ARS  | price_ars |
| Australia Dollar           | AUD  | price_aud |
| Bangladesh Taka            | BDT  | price_bdt |
| Brazil Real                | BRL  | price_brl |
| Bitcoin                    | BTC  | price_btc |
| Canada Dollar              | CAD  | price_cad |
| DR Congo Franc             | CDF  | price_cdf |
| Switzerland Franc          | CHF  | price_chf |
| Chile Peso                 | CLP  | price_clp |
| China Yuan Renminibi       | CNY  | price_cny |
| Colombian Peso             | COP  | price_cop |
| Czech Republic Koruna      | CZK  | price_czk |
| Denmark Krone              | DKK  | price_dkk |
| Egypt Pound                | EGP  | price_egp |
| Euro                       | EUR  | price_eur |
| United Kingdom Pound       | GBP  | price_gbp |
| Ghana Cedi                 | GHS  | price_ghs |
| Hong Kong Dollar           | HKD  | price_hkd |
| Hungary Forint             | HUF  | price_huf |
| Indonesia Rupiah           | IDR  | price_idr |
| Israel Rupee               | ILS  | price_ils |
| India Rupee                | INR  | price_inr |
| Japan Yen                  | JPY  | price_jpy |
| Korea Won                  | KRW  | price_krw |
| Mexico Peso                | MXN  | price_mxn |
| Malaysia Ringgit           | MYR  | price_myr |
| Nigeria Naira              | NGN  | price_ngn |
| Norway Krone               | NOK  | price_nok |
| New Zealand Dollar         | NZD  | price_nzd |
| Phillipines Piso           | PHP  | price_php |
| Pakistan Rupee             | PKR  | price_pkr |
| Poland Zloty               | PLN  | price_pln |
| Romania Leu                | RON  | price_ron |
| Russia Ruble               | RUB  | price_rub |
| Sweden Krone               | SEK  | price_sek |
| Singapore Dollar           | SGD  | price_sgd |
| Thailand Baht              | THB  | price_thb |
| Turkey Lire                | TRY  | price_try |
| Taiwan New Dollar          | TWD  | price_twd |
| Ukraine Hryvnia            | UAH  | price_uah |
| United States Dollar       | USD  | price_usd |
| Venezuela Bolívar Soberano | VES  | price_ves |
| Vietnam đồng               | VND  | price_vnd |
| South Africa Rand          | ZAR  | price_zar |

The `paymentId` will be stored in the vendor object. You can retrieve
this value using the `getPaymentId()` method if this is automatically
generated for you.

For example:

```php
$qrImgUrl = $vendor->getQr(9.95, 'GBP', '0abc123def456');
$paymentId = $vendor->getPaymentId();
```

#### Payment Widget
If you are an online vendor, you may want to use our [Vendor Payment
Widget](https://github.com/electroneum/vendor-payment-widget) which will
take the string returned from `getQr()` and display it as a QR code to
scan or a clickable link. THis is useful if someone visits your online
store on their mobile device. Please refer to our
[API Guide](https://community.electroneum.com/t/about-the-instant-payment-api-category/53)
for more details.

### Webhook

If you have provided a webhook URL in your [user vendor account](https://my.electroneum.com/user/vendor),
a signed webhook will be sent to this URL.

The user-agent sending the webhook will indicate the API version, the
URL to the integration guide for the API and will always start
`Electroneum/`. For example
`Electroneum/1.0 (+https://electroneum.com/instant-payments)`.

The payload will be sent in the request body and will include the event
type and the event details. The `event` will be either a `payment` or
a `refund`.

The signature is sent in the `ETN-SIGNATURE` HTTP header with the
webhook and is calculated using the SHA256 hash of the payload with
your API secret.

To verify the signature of a payload, you need to create the `Vendor`
object with your API key & secret:

```php
$vendor = new \Electroneum\Vendor\vendor(
    'key_live_1234567890abcdefghijklm',
    'sec_live_zxyxwvutsrqponmlkjihgfedcba0987654321zxyxwvutsrqponmlkj'
);
```

You can then verify a payload calling `verifySignature()` which will
return a boolean response:

```php
$payload = @file_get_contents('php://input');
$signature = @$_SERVER['HTTP_ETN_SIGNATURE'];
$verify = $vendor->verifySignature($payload, $signature);
```

To prevent replay attacks, the `verifySignature()` method will verify
that the timestamp is recent, within the last 5 minutes, to allow for
inaccurate clock synchronisations and for the customer to complete the
payment process.

We advise you to check against duplicate webhooks by making your event
processing idempotent; log a payment as processed so that if you get a
duplicate webhook payment id you can immediately ignore it.

If you receive a valid webhook signature, you are clear to proceed with
the transaction in the safe knowledge that the payment for the specified
amount in the payload has been sent.

#### Response

You must return a valid HTTP response code when receiving a webhook. To
prevent our webhook timing out, you should return an HTTP response as
soon as possible.

A success must return a 2XX code, for example 200. This will let us
know that you have received the webhook successfully and that it is
valid.

Any error should return a 4XX code, for example 401.

#### Failed Webhooks / Retries

Any response to our webhook that is not a successful code (not a 2XX)
will alert us that we should retry the webhook. Webhooks are retried
every hour for three days, after which they will not be retried.

To avoid receiving multiple webhooks, ensure your listener is idempotent
and that you return a successful HTTP response as soon as possible.

#### Generate Test Webhook

Once you have set up your vendor account, you will have the availability
to generate a test webhook from your [user vendor account](https://my.electroneum.com/user/vendor).

This will generate a random webhook payload and valid signature with
which you can test your receiver using a REST client like
[Insomnia REST Client](https://insomnia.rest). We do not push the
payload & signature to your webhook URL for this test.

### Poll for Payment Confirmation

If you are unable to open your network or ePOS to receive a webhook
notification, then you can alternatively poll our server to confirm
if a payment has been sent.

You will need to send your payment id and vendor address in JSON. A
signature of the payload will be creating using your secret key already
available in your vendor object:

```php
$result = $vendor->checkPaymentPoll(json_encode([
    'payment_id' => '7ce25b4dc0',
    'vendor_address' => 'etn-it-0abc123def456'
]));
```

The resulting JSON will include:
- `status`<br />`0` Payment not sent<br />`1` Payment sent
- `message`<br />If the status is `0`, an error message will be returned.
- `amount`<br />If the status is `1`, an amount will returned of ETN paid
  by the customer.

To avoid being blocked by our API, please restrict your poll to no more
than one request per second.
