<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use QueenAurelia\SOTA_Client\Qso;

final class QsoTest extends TestCase
{
    public function testConstructor(): void
    {
        $a = new Qso();
        $this->assertInstanceOf(Qso::class, $a);
    }

    public function testJsonSerialize(): void
    {
        $expectedJsonObj = [
            'callsign' => "W0KEH",
            'date' => '2025-05-29',
            'time' => '23:23',
            'mode' => 'SSB',
            'band' => '14.310MHz',
            'comments' => 'Testing',
            's2sSummitCode' => 'TEST'
        ];

        $qso = new Qso()
            ->callsign($expectedJsonObj['callsign'])
            ->date($expectedJsonObj['date'])
            ->time($expectedJsonObj['time'])
            ->mode($expectedJsonObj['mode'])
            ->band($expectedJsonObj['band'])
            ->comments($expectedJsonObj['comments'])
            ->s2sSummitCode($expectedJsonObj['s2sSummitCode']);

        // The SOTA API expects dates in d/m/Y format
        $expectedJsonObj['date'] = new DateTimeImmutable($expectedJsonObj['date'])->format('d/m/Y');
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJsonObj), json_encode($qso));
    }
}
