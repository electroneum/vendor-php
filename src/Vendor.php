<?php

namespace Electroneum\Vendor;

use Electroneum\Vendor\Exception\VendorException;

class Vendor
{
    /**
     * Url to poll for payment confirmation.
     */
    const URL_POLL = 'https://poll.electroneum.com/vendor/check-payment';

    /**
     * Url for the exchange rate JSON.
     */
    const URL_SUPPLY = 'https://supply.electroneum.com/app-value-v2.json';

    /**
     * Url (sprintf) to load a QR code.
     */
    const URL_QR = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=%s';

    /**
     * @var array
     *
     * Currencies accepted for converting to ETN.
     */
    protected static $currencies = [
        'ARS',
        'AUD',
        'BDT',
        'BRL',
        'BTC',
        'CAD',
        'CDF',
        'CHF',
        'CLP',
        'CNY',
        'COP',
        'CZK',
        'DKK',
        'EGP',
        'EUR',
        'GBP',
        'GHS',
        'HKD',
        'HUF',
        'IDR',
        'ILS',
        'INR',
        'JPY',
        'KHR',
        'KRW',
        'MXN',
        'MYR',
        'NGN',
        'NOK',
        'NZD',
        'PHP',
        'PKR',
        'PLN',
        'RON',
        'RUB',
        'SEK',
        'SGD',
        'THB',
        'TRY',
        'TWD',
        'UAH',
        'USD',
        'VES',
        'VND',
        'ZAR',
    ];

    /**
     * @var string
     *
     * Your vendor API key.
     */
    private $apiKey;

    /**
     * @var string
     *
     * Your vendor API secret.
     */
    private $apiSecret;

    /**
     * @var float
     *
     * The amount to charge in ETN.
     */
    private $etn;

    /**
     * @var string
     *
     * The outlet id.
     */
    private $outlet;

    /**
     * @var string
     *
     * The payment id.
     */
    private $paymentId;

    /**
     * Get etn
     *
     * @return float
     */
    public function getEtn()
    {
        return $this->etn;
    }

    /**
     * Get outlet
     *
     * @return string
     */
    public function getOutlet()
    {
        return $this->outlet;
    }

    /**
     * Get paymentId
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Instantiate a new Electroneum vendor client.
     *
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($apiKey = null, $apiSecret = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Generate a cryptographic random payment id.
     *
     * @return string
     * @throws VendorException
     */
    public function generatePaymentId()
    {
        try {
            $this->paymentId = bin2hex(random_bytes(5));

            return $this->paymentId;
        } catch (\Exception $e) {
            // CryptGenRandom (Windows), getrandom(2) (Linux) or /dev/urandom (others) was unavailable to generate random bytes.
            throw new VendorException($e->getMessage());
        }
    }

    /**
     * Convert a local currency amount to ETN.
     *
     * @param $value
     * @param $currency
     * @return float|string
     * @throws VendorException
     */
    public function currencyToEtn($value, $currency)
    {
        // Check the currency is accepted.
        if (!in_array(strtoupper($currency), self::$currencies, true)) {
            throw new VendorException('Unknown currency');
        }

        // Get the JSON conversion data.
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::URL_SUPPLY);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $json = curl_exec($ch);
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $json = file_get_contents(self::URL_SUPPLY);
        } else {
            throw new VendorException('No extension available to fetch currency conversion JSON');
        }

        // Check if the JSON data has been received.
        if (empty($json)) {
            throw new VendorException('Could not load currency conversion JSON');
        }

        // Check the JSON is valid.
        $arr = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VendorException('Could not load valid currency conversion JSON');
        }

        // Get the conversion rate.
        if (!$rate = $arr['price_' . strtolower($currency)]) {
            throw new VendorException('Currency rate not found');
        }

        // Check the rate is valid or more than zero (the following division would also fail).
        if ((float)$rate <= 0) {
            throw new VendorException('Currency conversion rate not valid');
        }

        if (extension_loaded('bcmath')) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            $this->etn = bcdiv($value, $rate, 2);
        } else {
            $this->etn = number_format((float)$value / $rate, 2, '.', '');
        }

        return $this->etn;
    }

    /**
     * Generate a QR code for a vendor transaction.
     *
     * @param float|string $amount
     * @param string $outlet
     * @param string $paymentId
     * @return string
     * @throws VendorException
     */
    public function getQrCode($amount, $outlet, $paymentId = null)
    {
        // Generate/validate the paymentId.
        if ($paymentId === null) {
            $paymentId = $this->generatePaymentId();
        } elseif (strlen($paymentId) !== 10 || !ctype_xdigit($paymentId)) {
            throw new VendorException('Qr code payment id is not valid');
        }
        $this->paymentId = $paymentId;

        // Validate the amount.
        if (empty($amount) && is_numeric($amount)) {
            throw new VendorException('Qr code amount is not valid');
        }
        $this->etn = (float)$amount;

        // Validate the outlet.
        if (empty($outlet) || !ctype_xdigit($outlet)) {
            throw new VendorException('Qr code outlet is not valid');
        }
        $this->outlet = $outlet;

        // Return the QR code string.
        return sprintf('etn-it-%s/%s/%.2f', $this->outlet, $this->paymentId, $this->etn);
    }

    /**
     * Return a QR image Url for a QR code string.
     *
     * @param string $qrCode
     * @return string
     */
    public function getQrUrl($qrCode)
    {
        return sprintf(self::URL_QR, urlencode($qrCode));
    }

    /**
     * Return a QR image Url for given data (grouping the above functions into one).
     *
     * @param float $amount
     * @param string $currency
     * @param string $outlet
     * @param string $paymentId
     * @return string
     * @throws VendorException
     */
    public function getQr($amount, $currency, $outlet, $paymentId = null)
    {
        // Convert the currency.
        $etn = $this->currencyToEtn($amount, $currency);

        // Build the QR Code string.
        $qrCode = $this->getQrCode($etn, $outlet, $paymentId);

        return $this->getQrUrl($qrCode);
    }

    /**
     * Validate a webhook signature based on a payload.
     *
     * @param string $payload
     * @param string $signature
     * @return boolean
     * @throws VendorException
     */
    public function verifySignature($payload, $signature)
    {
        // Check we have a vendor API key.
        if (empty($this->apiKey)) {
            throw new VendorException('No vendor API key set');
        }

        // Check we have a vendor API secret.
        if (empty($this->apiSecret)) {
            throw new VendorException('No vendor API secret set');
        }

        // Check we have a valid payload.
        $payload = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VendorException('Verify signature `payload` is not valid enc');
        }
        if (empty($payload)) {
            throw new VendorException('Verify signature `payload` is not valid empty');
        }

        // Check we have a valid signature.
        if (empty($signature) || strlen($signature) !== 64 || !ctype_xdigit($signature)) {
            throw new VendorException('Verify signature `signature` is not valid');
        }

        // Validate the signature.
        if ($payload['key'] !== $this->apiKey) {
            // This isn't the payload you are looking for.
            return false;
        }

        // Invalid webhook signature.
        if ($signature !== hash_hmac('sha256', json_encode($payload), $this->apiSecret)) {
            return false;
        }

        // Expired webhook.
        if (strtotime($payload['timestamp']) < strtotime('-5 minutes')) {
            return false;
        }

        // Valid webhook signature.
        return true;
    }

    /**
     * Generate a signature for a payload.
     *
     * @param string $payload
     * @return string
     * @throws VendorException
     */
    public function generateSignature($payload)
    {
        // Check we have a vendor API secret.
        if (empty($this->apiSecret)) {
            throw new VendorException('No vendor API secret set');
        }

        // Check we have a valid payload.
        $payloadArray = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payloadArray) || empty($payloadArray)) {
            throw new VendorException('Generate signature `payload` is not valid');
        }

        // Validate the signature.
        return hash_hmac('sha256', $payload, $this->apiSecret);
    }

    /**
     * Poll the API to check a vendor payment. The signature will be generated if not supplied.
     *
     * @param string $payload
     * @param string $signature
     * @return array
     * @throws VendorException
     */
    public function checkPaymentPoll($payload, $signature = null)
    {
        // Generate the signature, if we need to.
        if (empty($signature)) {
            $signature = $this->generateSignature($payload);
        }

        // Check the signature length.
        if (strlen($signature) !== 64) {
            throw new VendorException('Check payment signature length invalid');
        }

        // cURL the payload with the signature to the API.
        if ($ch = curl_init(self::URL_POLL)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'ETN-SIGNATURE: ' . $signature
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            throw new VendorException('Check payment cURL cannot initialise');
        }

        // Return the result as an array.
        return json_decode($result, true);
    }
}
