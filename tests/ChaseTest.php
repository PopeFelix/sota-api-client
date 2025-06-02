<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use QueenAurelia\SOTA_Client\Chase;

final class ChaseTest extends TestCase {
    public function testConstructor(): void
    {
        $a = new Chase();
        $this->assertInstanceOf(Chase::class, $a);
    }

    public function testJsonSerialize(): void 
    {
        $expected = [
            'date' => '2025-05-29',
            'timeStr' => '23:23',
            'ownCallsign' => 'w0keh',
            'otherCallsign' => 'W1AW',
            'summitCode' => 'W3/PW-024',
            's2sSummitCode' => 'JA/NMN-181',
            'band' => '14MHz',
            'mode' => 'CW',
            'comments' => 'Testing'
        ];

        $chase = new Chase()
            ->date($expected['date'])
            ->timeStr($expected['timeStr'])
            ->ownCallsign($expected['ownCallsign'])
            ->otherCallsign($expected['otherCallsign'])
            ->summitCode($expected['summitCode'])
            ->s2sSummitCode($expected['s2sSummitCode'])
            ->band($expected['band'])
            ->mode($expected['mode'])
            ->comments($expected['comments']);

        // The SOTA API expects dates in d/m/Y format
        $expected['date'] = new DateTimeImmutable($expected['date'])->format('d/m/Y');

        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($chase));
    }
    
}