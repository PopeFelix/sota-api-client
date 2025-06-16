<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use QueenAurelia\SOTA_Client\Client;
use QueenAurelia\SOTA_Client\Activation;
use Dotenv\Dotenv;
use QueenAurelia\SOTA_Client\Qso;

/**
 * This runs a LIVE test against the SOTA API. 
 */
final class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();

        if (! ($_ENV['SOTA_USERNAME'] || $_ENV['SOTA_PASSWORD'] || $_ENV['SOTA_CLIENT_ID'])) {
            $this->markTestSkipped("SOTA API credentials not set in environment. Cannot proceed.");
        }
    }

    public function testIntegration(): void {
        $this->expectNotToPerformAssertions();

        $today = new DateTime()->format("Y-m-d");
        $now = new DateTime()->format("G:i");
        $username = $_ENV['SOTA_USERNAME'];
        $password = $_ENV['SOTA_PASSWORD'];
        $client_id = $_ENV['SOTA_CLIENT_ID'];
        $summit =  'W0I/IA-002';
        $client = new Client(['client_id' => $client_id, 'username' => $username, 'password' => $password]);
        $activation = new Activation()
            ->date($today)
            ->ownCallsign($username)
            ->summit($summit)
            ->qsos([
                new Qso()
                    ->callsign("$username/0")
                    ->date($today)
                    ->time($now)
                    ->mode('SSB')
                    ->band('14.310MHz')
                    ->comments('DUMMY QSO')
            ]);
        $client->addActivation($activation);
        try {
            $client->upload();
        } catch (Exception $e) {
            $this->fail("Caught exception in upload: $e");
        }
                
    }
}
