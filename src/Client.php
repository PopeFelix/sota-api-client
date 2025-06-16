<?php

declare(strict_types=1);

namespace QueenAurelia\SOTA_Client;

use Exception;
use QueenAurelia\SOTA_Client\Exception\InvalidArgumentException;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\HandlerStack;
use QueenAurelia\SOTA_Client\Exception\AccessDeniedException;
use QueenAurelia\SOTA_Client\Exception\InvalidClientIdException;
use QueenAurelia\SOTA_Client\Exception\ServerException;

/**
 * @psalm-api
 * 
 * SOTA database API client
 * 
 * Usage: 
 * 
 * // Instantiate the client (will automatically log in)
 * 
 * use QueenAurelia\SOTA_Client\Client; 
 * use QueenAurelia\SOTA_Client\Activation; 
 * use QueenAurelia\SOTA_Client\QSO;
 * use QueenAurelia\SOTA_Client\Chase;
 * 
 * $client = new Client([
 *    'username'   => 'w0keh',
 *    'password'   => 'mYp@55w0rd!',
 *    'client_id'  => 'wavelog'
 * ]);
 * 
 * $qsos = [
 *   new Qso()->date("2025-05-29")->time("23:23")->callsign("W1AW")->mode("CW")->band("14.310MHz"),
 *   new Qso()->date("2025-05-29")->time("23:23")->callsign("K0GQ")->mode("CW")->band("14.310MHz"),
 * ];
 * 
 * $client->addActivation(new Activation()->callsign('W0KEH')->date->('2025-05-29')->summit('VK3/VC-030')->qsos($qsos);
 * $client->addChase(new Chase()->date("2025-05-29")->timeStr("23:23")->otherCallsign("W0KEH/1")->ownCallsign("W0KEH")->s2sSummitCode("JA/NN-181")->mode("CW")->band("7Mhz"));
 * $client->upload();
 */
final class Client
{
    private string $clientId;
    private string $refresh_token;
    private string $access_token;
    private ?HandlerStack $handlerStack;
    private UploadData $uploadData;

    private const SOTA_OIDC_URL_BASE = "https://sso.sota.org.uk/auth/realms/SOTA/protocol/openid-connect";
    private const SOTA_API_URL_BASE = "https://api-db2.sota.org.uk/";

    /**
     * Create a new instance of the SOTA API client
     * 
     * @param array $config Client configuration
     *   $config = [
     *      'client_id' => (string) SOTA API client ID. Required.,
     *      'handler' => (HandlerStack) Pass this option to specify a custom HTTP handler {@link https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html},
     *      'username' => SOTA database username
     *      'password' => SOTA database password
     *   ]
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['client_id'])) {
            throw new InvalidArgumentException("Missing required parameter 'client_id'");
        }
        $this->clientId = $config['client_id'];
        if (isset($config['handler'])) {
            $this->handlerStack = $config['handler'];
        } else {
            $this->handlerStack = null;
        }
        $this->login($config['username'], $config['password']);
        $this->uploadData = new UploadData();
    }

    public function __destruct()
    {
        try {
            $this->logout();
        } catch (Exception $_e) {
            // Swallow any exceptions
        }
    }

    private function login(string $username, string $password): void
    {
        $http = $this->getHttpClient();
        $res = $http->post(self::SOTA_OIDC_URL_BASE . '/token', ['form_params' => ['client_id' => $this->clientId, 'grant_type' => 'password', 'username' => $username, 'password' => $password]]);
        $code = $res->getStatusCode();
        $body = (string) $res->getBody();

        $contentType = $res->getHeader('Content-Type')[0];
        if ($contentType != 'application/json') {
            throw new ServerException("Unexpected content type \"$contentType\" received from server");
        }
        $json = json_decode($body, true);

        if ($code == 401) {
            if ($json['error'] == 'invalid_client') {
                throw new InvalidClientIdException($json['error_description']);
            } else {
                throw new AccessDeniedException($json['error_description']);
            }
        } elseif ($code != 200) {
            throw new ServerException(sprintf("(%s) %s", $json['error'], $json['error_description']));
        }
        $this->access_token = $json['access_token'];
        $this->refresh_token = $json['refresh_token'];
    }

    /**
     * Log out of the SOTA API. It is *recommended* that you do this when you are finished.
     */
    public function logout(): void
    {
        $http = $this->getHttpClient();
        $url = self::SOTA_OIDC_URL_BASE . '/logout';
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token
            ],
            'client_id' => $this->clientId,
            'refresh_token' => $this->refresh_token
        ];
        $http->post($url, $options);
    }

    /**
     * Add an activation to be uploaded to the SOTA database.
     * 
     * @param Activation $activation The activation record to be added to the upload request
     *  
     */
    public function addActivation(Activation $activation): void
    {
        $this->uploadData->addActivation($activation);
    }

    /**
     * Add a chase to be upload to the SOTA database
     * 
     * @param Chase $chase The chase record to be added to the upload request
     */
    public function addChase(Chase $chase): void
    {
        $this->uploadData->addChase($chase);
    }

    /**
     * Upload activations and/or chases to the SOTA database. 
     */
    public function upload(): void
    {
        $http = $this->getHttpClient(['with_auth' => true]);
        $url = self::SOTA_API_URL_BASE . '/uploads';
        $res = $http->post($url, ['json' => $this->uploadData]);
        $code = $res->getStatusCode();
        $body = (string) $res->getBody();
        if ($code != 200) {
            if ($code == 401 || $code == 403) {
                throw new AccessDeniedException($body);
            } else {
                throw new ServerException($body);
            }
        }
    }

    // TODO
    // /**
    //  * Verify uploads before submitting them to the database
    //  * 
    //  * @param UploadDataInterface $upload Data to be uploaded
    //  */
    // public function verifyUpload(UploadDataInterface $upload): void {
    //     $http = new Http(array('http_errors' => true, 'headers' => array('Authorization' => 'Bearer ' . $this->access_token)));
    //     $url = self::SOTA_API_URL_BASE . '/uploads/verify';
    //     $res = $http->post($url, ['json' => $upload]);
    //     $code = $res->getStatusCode();
    //     $body = (string) $res->getBody();
    // }

    // Helper method to instantiate a Guzzle HTTP client
    private function getHttpClient(array $options = []): GuzzleHttpClient
    {
        if (!isset($options['http_errors'])) {
            $options['http_errors'] = false;
        }
        if (isset($options['with_auth']) && $options['with_auth']) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }
        if ($this->handlerStack) {
            $options['handler'] = $this->handlerStack;
        }
        return new GuzzleHttpClient($options);
    }
}
