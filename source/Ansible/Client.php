<?php
/**
 * Created by PhpStorm.
 * User: jaune
 * Date: 02/08/14
 * Time: 16:28
 */

namespace Ansible;

use Guzzle\Http\Client as GuzzleClient;


class Client {

    private $guzzle;
    private $key;
    private $secret;

    private $host = 'localhost:8081';
    private $protocol = 'http';

    public
    function __construct($api_key, $api_secret) {
        $this->key = $api_key;
        $this->secret = $api_secret;
        $this->guzzle = new GuzzleClient();
    }



    public
    function requestTokenAnonymousToAccount($account, $session = null) {

        $requestBody = (object)[
            'type' => 'anonymous->account',
            'account' => $account,
            'session' => $session
        ];

        $req = $this->guzzle->post($this->protocol.'://'.$this->host.'/token', [
            'Content-Type' => 'application/json',
        ], json_encode($requestBody));

        $req->setAuth($this->key, $this->secret);

        $res = $req->send();

//        echo $res->getStatusCode();           // 200
//        echo $res->getHeader('content-type'); // 'application/json; charset=utf8'

        $body = @json_decode($res->getBody());
        if (!is_object($body)) {
            throw new \RuntimeException();
        }
        if (!isset($body->token)) {
            throw new \RuntimeException();
        }
        if (!is_string($body->token)) {
            throw new \RuntimeException();
        }
        return $body;
    }

    public function requestTokenAccount($account, $session = null)
    {

        $requestBody = (object)[
            'type' => 'account',
            'account' => $account,
            'session' => $session,
        ];

        $req = $this->guzzle->post($this->protocol.'://'.$this->host.'/token', [
            'Content-Type' => 'application/json',
        ], json_encode($requestBody));

        $req->setAuth($this->key, $this->secret);

        $res = $req->send();

//        echo $res->getStatusCode();           // 200
//        echo $res->getHeader('content-type'); // 'application/json; charset=utf8'

        $body = @json_decode($res->getBody());
        if (!is_object($body)) {
            throw new \RuntimeException();
        }
        if (!isset($body->token)) {
            throw new \RuntimeException();
        }
        if (!is_string($body->token)) {
            throw new \RuntimeException();
        }
        return $body;
    }

}