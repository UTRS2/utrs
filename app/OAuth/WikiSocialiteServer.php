<?php

namespace App\OAuth;

use Exception;
use Carbon\Carbon;
use League\OAuth1\Client\Server\User;
use League\OAuth1\Client\Server\Server;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth1\Client\Credentials\TokenCredentials;

class WikiSocialiteServer extends Server
{
    protected $responseType = 'string';

    public function __construct($clientCredentials)
    {
        parent::__construct($clientCredentials);
        $this->signature = new WikiHmacSha1Signature($this->clientCredentials);
    }

    private function getBaseUrl()
    {
        return env('OAUTH_BASE_URL');
    }

    public function urlTemporaryCredentials()
    {
        return $this->getBaseUrl() . '/w/index.php?title=Special:OAuth/initiate';
    }

    public function urlAuthorization()
    {
        return $this->getBaseUrl() . '/w/index.php?title=Special:OAuth/authorize';
    }

    public function urlTokenCredentials()
    {
        return $this->getBaseUrl() . '/w/index.php?title=Special:OAuth/token';
    }

    public function urlUserDetails()
    {
        return $this->getBaseUrl() . '/w/index.php?title=Special:OAuth/identify';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User;
        $user->uid = $data->username;
        $user->name = $data->username;
        $user->email = $data->email;
        $user->groups = $data->groups;
        return $user;
    }

    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data->username;
    }

    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $data->email;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data->username;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    // Overriding this is a hack, and I don't like it. Upstream class does not support JWTs as user details so I made this class support it.
    public function getUserDetails(TokenCredentials $tokenCredentials, $force = false)
    {
        if (!$this->cachedUserDetailsResponse || $force) {
            $url = $this->urlUserDetails();

            $client = $this->createHttpClient();

            $headers = $this->getHeaders($tokenCredentials, 'GET', $url);

            try {
                $response = $client->get($url, [
                    'headers' => $headers,
                ]);
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
                $body = $response->getBody();
                $statusCode = $response->getStatusCode();

                throw new Exception(
                    "Received error [$body] with status code [$statusCode] when retrieving token credentials."
                );
            }

            [$headerBase64, $payloadBase64, $signature] = explode('.', (string) $response->getBody());
            $headerStr = base64_decode($headerBase64);
            $payloadStr = base64_decode($payloadBase64);

            $payload = json_decode($payloadStr);

            if (!$this->verifyJwt($headerStr, $payloadStr, $payload, $signature)) {
                throw new Exception('Failed to verify JWT signature');
            }

            $this->cachedUserDetailsResponse = $payload;
        }

        return $this->cachedUserDetailsResponse;
    }

    private function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    private function verifyJwt($headerStr, $payloadStr, $payload, $signature)
    {
        $expiration = Carbon::createFromTimestamp($payload->exp);
        $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

        $encodedHeader = $this->base64UrlEncode($headerStr);
        $encodedPayload = $this->base64UrlEncode($payloadStr);
        $validSignature = hash_hmac('sha256', $encodedHeader . "." . $encodedPayload, $this->clientCredentials->getSecret(), true);

        $signatureValid = $signature === $this->base64UrlEncode($validSignature);

        return !$tokenExpired && $signatureValid;
    }
}