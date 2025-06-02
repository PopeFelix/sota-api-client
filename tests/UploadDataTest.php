<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use QueenAurelia\SOTA_Client\Activation;
use QueenAurelia\SOTA_Client\Chase;
use QueenAurelia\SOTA_Client\Qso;
use QueenAurelia\SOTA_Client\UploadData;

/**
 * @psalm-type QsoJson = array{callsign: string, date: string, time: string, mode: string, band: string, comments: string, s2sSummitCode: string}
 * @psalm-type ActivationJson = array{date: string, ownCallsign: string, summit: string, qsos: QsoJson[]}
 * @psalm-type ChaseJson = array{date: string, timeStr: string, ownCallsign: string, otherCallsign: string, summitCode: string, band: string, mode: string, comments: string}
 */ 
final class UploadDataTest extends TestCase
{
    /** @var ActivationJson[] */
    private array $activations = [];
    /** @var ChaseJson[] */
    private array $chases = [];
   
    protected function setUp(): void {
        $this->activations = [
            [
                'date' => '2025-05-29',
                'ownCallsign' => 'w0keh',
                'summit' => 'W3/PW-024',
                'qsos' =>  [
                    [
                        'callsign' => "W0KEH/0",
                        'date' => '2025-05-29',
                        'time' => '23:23',
                        'mode' => 'SSB',
                        'band' => '14.310MHz'
                    ],
                    [
                        'callsign' => "W0KEH/1",
                        'date' => '2025-05-29',
                        'time' => '23:23',
                        'mode' => 'SSB',
                        'band' => '14.310MHz',
                        's2sSummitCode' => 'TEST1'
                    ]
                ]
            ],
            [
                'date' => '2025-05-28',
                'ownCallsign' => 'w0keh',
                'summit' => 'W4/PW-024',
                'qsos' =>  [
                    [
                        'callsign' => "W0KEH/0",
                        'date' => '2025-05-28',
                        'time' => '23:23',
                        'mode' => 'SSB',
                        'band' => '14.310MHz'
                    ],
                    [
                        'callsign' => "W0KEH/1",
                        'date' => '2025-05-28',
                        'time' => '23:23',
                        'mode' => 'SSB',
                        'band' => '14.310MHz',
                        's2sSummitCode' => 'TEST2'
                    ]
                ]
            ]
        ];

        $this->chases = [
            [
                'date' => '2025-05-29',
                'timeStr' => '23:23',
                'ownCallsign' => 'w0keh',
                'otherCallsign' => 'W1AW',
                's2sSummitCode' => 'JA/NMN-181',
                'band' => '14MHz',
                'mode' => 'CW',
            ],
            [
                'date' => '2025-05-28',
                'timeStr' => '23:23',
                'ownCallsign' => 'w0keh',
                'otherCallsign' => 'W1AW',
                's2sSummitCode' => 'JA/NMN-181',
                'band' => '14MHz',
                'mode' => 'CW',
            ],
        ];

        
    }
    public function testConstructor(): void
    {
        $u = new UploadData();
        $this->assertInstanceOf(UploadData::class, $u);
    }

    public function testAddActivation(): void
    {
        $activationData = [
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
        $u = new UploadData();
        $activation = new Activation()
            ->date($activationData['date'])
            ->ownCallsign($activationData['ownCallsign'])
            ->summit($activationData['summit'])
            ->qsos(array_map(function (array $qso): Qso {
                return new Qso()->callsign($qso['callsign'])->date($qso['date'])->time($qso['time'])->mode($qso['mode'])->band($qso['band']);
            }, $activationData['qsos']));
        try {
            $u->addActivation($activation);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail("addActivation() threw an exception. \"{$e->getMessage()}\"");
        }
    }

    public function testAddChase(): void
    {
        $chaseData = [
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
        $u = new UploadData();
        $chase = new Chase()
            ->date($chaseData['date'])
            ->timeStr($chaseData['timeStr'])
            ->ownCallsign($chaseData['ownCallsign'])
            ->summitCode($chaseData['summitCode'])
            ->s2sSummitCode($chaseData['s2sSummitCode'])
            ->band($chaseData['band'])
            ->mode($chaseData['mode'])
            ->comments($chaseData['comments']);
        try {
            $u->addChase($chase);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail("addChase() threw an exception. \"{$e->getMessage()}\"");
        }
    }

    public function testJsonSerialize(): void
    {
        $u = new UploadData();
        foreach ($this->activations as $a) {
            $activation = new Activation()
                ->date($a['date'])
                ->ownCallsign($a['ownCallsign'])
                ->summit($a['summit'])
                ->qsos(array_map(function (array $qso): Qso {
                    $new = new Qso()->callsign($qso['callsign'])->date($qso['date'])->time($qso['time'])->mode($qso['mode'])->band($qso['band']);
                    foreach (array('comments', 's2sSummitCode') as $key) {
                        if (isset($qso[$key]) && $qso[$key]) {
                            $new = $new->$key($qso[$key]);
                        }
                    }
                    return $new;
                }, $a['qsos']));
            $u->addActivation($activation);
        }

        foreach ($this->chases as $c) {       
            $chase = new Chase()
                ->date($c['date'])
                ->timeStr($c['timeStr'])
                ->ownCallsign($c['ownCallsign'])
                ->s2sSummitCode($c['s2sSummitCode'])
                ->band($c['band'])
                ->mode($c['mode']);
            foreach (array('comments', 'summitCode', 'otherCallsign') as $key) {
                if (isset($c[$key]) && $c[$key]) {
                    $chase = $chase->$key($c[$key]);
                }
            }
            $u->addChase($chase);
        }

        $uploadObj = $u->jsonSerialize();

        foreach ($this->activations as $i => $expectedActivation) {
            $gotActivation = $uploadObj['activations'][$i];
            $expectedActivation = $this->activations[$i];
            foreach (array_keys($expectedActivation) as $key) {
                $got = $gotActivation[$key];
                $expected = $expectedActivation[$key];
                if ($key == 'qsos') {
                    for ($j = 0; $j < count($expectedActivation['qsos']); $j++) {
                        $gotQso = $gotActivation['qsos'][$j];
                        $expectedQso = $expectedActivation['qsos'][$j];
                        foreach (array_keys($expectedQso) as $key) {
                            $got = $gotQso[$key];
                            $expected = $expectedQso[$key];
                            if ($key == 'date') {
                                $expected = new DateTimeImmutable($expected)->format("d/m/Y");
                            }
                            $this->assertEquals($expected, $got, "Mismatch for \"$key\" in activation $i, QSO $j: expected \"$expected\", got \"$got\"");
                        }
                    }
                } else {
                    if ($key == 'date') {
                        $expected = new DateTimeImmutable($expected)->format("d/m/Y");
                    }
                    $this->assertEquals($expected, $got, "Mismatch for \"$key\" in activation $i: expected \"$expected\", got \"$got\"");
                }
            }
        }

        foreach ($this->chases as $i => $expectedChase) {
            $gotChase = $uploadObj['chases'][$i];
            $expectedChase = $this->chases[$i];
            foreach (array_keys($expectedChase) as $key) {
                $got = $gotChase[$key];
                $expected = $expectedChase[$key];
                if ($key == 'qsos') {
                    for ($j = 0; $j < count($expectedChase['qsos']); $j++) {
                        $gotQso = $gotChase['qsos'][$j];
                        $expectedQso = $expectedChase['qsos'][$i];
                        foreach (array_keys($expectedQso) as $key) {
                            $got = $gotQso[$key];
                            $expected = $expectedQso[$key];
                            if ($key == 'date') {
                                $expected = new DateTimeImmutable($expected)->format("d/m/Y");
                            }
                            $this->assertEquals($expected, $got, "Mismatch for \"$key\" in chase $i, QSO $j: expected \"$expected\", got \"$got\"");
                        }
                    }
                } else {
                    if ($key == 'date') {
                        $expected = new DateTimeImmutable($expected)->format("d/m/Y");
                    }
                    $this->assertEquals($expected, $got, "Mismatch for \"$key\" in chase $i: expected \"$expected\", got \"$got\"");
                }
            }
            
        }
        // $this->assertEquals($activationData, $uploadObj['activations']);
        // $this->assertEquals($chaseData, $uploadObj['chases']);
            
        foreach ($this->activations as $activation) {
            // Find the S2S records from this activation
            $s2sChases = array_filter($uploadObj['s2s'], function (array $s2s) use ($activation): bool {
                return $s2s['summitCode'] == $activation['summit'];
            });
            $this->assertNotCount(0, $s2sChases);
            foreach ($s2sChases as $s2sChase) {
                // Get the QSO from the activation that matches this S2S QSO
                
                $summitCode = $s2sChase['s2sSummitCode'];
                $matchingQso = null;
                foreach ($activation['qsos'] as $qso) {
                    if (isset($qso['s2sSummitCode']) && $qso['s2sSummitCode'] == $summitCode) {
                        $matchingQso = $qso;
                    }
                }

                foreach (['date', 's2sSummitCode', 'mode', 'band', 'comments'] as $key) {
                    $got = $s2sChase[$key];
                    $expected = $matchingQso[$key];
                    if ($key == 'date') {
                        $expected = new DateTimeImmutable($expected)->format("d/m/Y");
                    }
                    $this->assertEquals($expected, $got, "\"$key\" in S2S chase (\"$got\") does not match QSO in activation record (\"$expected\")");
                }
                $this->assertEquals($matchingQso['callsign'], $s2sChase['otherCallsign']);
                $this->assertEquals($matchingQso['time'], $s2sChase['timeStr']);
                $this->assertEquals($activation['ownCallsign'], $s2sChase['ownCallsign']);
                $this->assertEquals($activation['summit'], $s2sChase['summitCode']);
            }
        }
    }
}
