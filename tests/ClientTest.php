<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use QueenAurelia\SOTA_Client\Client;

const SOTA_OIDC_URL_BASE = "https://sso.sota.org.uk/auth/realms/SOTA/protocol/openid-connect";
const SOTA_API_URL_BASE = "https://api-db2.sota.org.uk/";

final class ClientTest extends TestCase
{
    public function testConstructor(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'test_access_token',
                'refresh_token' => 'test_refresh_token'
            ]))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $username = 'test_username';
        $password = 'test_password';
        $client = new Client(['client_id' => 'test', 'handler' => $handlerStack, 'username' => $username, 'password' => $password]);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(1, $container, "One request");
        /** @var array({request: GuzzleHttp\Psr7\Request }) $txn */
        foreach ($container as $txn) {
            /** @var GuzzleHttp\Psr7\Request $req */
            $req = $txn['request'];
            $method = $req->getMethod();
            $url = sprintf("%s", $req->getUri());
            $expectedUrl = SOTA_OIDC_URL_BASE . "/token";
            $this->assertEquals($expectedUrl, $url);
            $this->assertEquals('POST', $method);
            // TODO: Test request body
        }
    }
}
