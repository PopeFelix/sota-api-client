<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use QueenAurelia\SOTA_Client\Activation;
use QueenAurelia\SOTA_Client\Qso;

final class ActivationTest extends TestCase
{
    public function testConstructor(): void
    {
        $a = new Activation();
        $this->assertInstanceOf(Activation::class, $a);
    }

    public function testJsonSerialize(): void
    {
        $expectedJsonObj = [
            'date' => '2025-05-29',
            'ownCallsign' => 'w0keh',
            'summit' => 'W3/PW-024',
            'qsos' => array_map(function ($i) {
                return [
                    'callsign' => "W0KEH/$i",
                    'date' => '2025-05-29',
                    'time' => '23:23',
                    'mode' => 'SSB',
                    'band' => '14.310MHz'
                ];
            }, range(1, 3)),
        ];
        $qsos = array_map(function ($qso) {
            $new = new Qso()
                ->callsign($qso['callsign'])
                ->date($qso['date'])
                ->time($qso['time'])
                ->mode($qso['mode'])
                ->band($qso['band']);
            foreach (['comments', 's2sSummitCode'] as $key) {
                if (isset($qso[$key])) {
                    $new = $new->$key($qso[$key]);
                }
            }
            return $new;
        }, $expectedJsonObj['qsos']);

        $activation = new Activation()
            ->date($expectedJsonObj['date'])
            ->ownCallsign($expectedJsonObj['ownCallsign'])
            ->summit($expectedJsonObj['summit'])
            ->qsos($qsos);

        // The SOTA API expects dates in d/m/Y format
        $expectedJsonObj['date'] = new DateTimeImmutable($expectedJsonObj['date'])->format('d/m/Y');
        for ($i = 0; $i < count($expectedJsonObj['qsos']); $i++) {
            $date = $expectedJsonObj['qsos'][$i]['date'];
            $expectedJsonObj['qsos'][$i]['date'] = new DateTimeImmutable($date)->format('d/m/Y');
            foreach (['comments', 's2sSummitCode'] as $key) {
                if (!isset($qso[$key])) {
                    $expectedJsonObj['qsos'][$i][$key] = "";
                }
            }
        }
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJsonObj), json_encode($activation));
    }
}
