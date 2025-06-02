<?php

declare(strict_types=1);

namespace QueenAurelia\SOTA_Client;

use Exception;
use JsonSerializable;

final class UploadData implements JsonSerializable
{
    /** @var Chase[] */
    private array $chases = [];

    /** @var Activation[] */
    private array $activations = [];

    /**
     * Add an activation
     *
     * @param Activation $activation 
     */
    public function addActivation(Activation $activation): void
    {
        array_push($this->activations, $activation);
    }

    /**
     * Add a chase
     *
     * @param Chase $chase
     */
    public function addChase(Chase $chase): void
    {
        array_push($this->chases, $chase);
    }

    /**
     * @return array[]
     *
     * @psalm-return array{activations: array, s2s: array, chases: array}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        $activations = [];
        $s2s = [];
        foreach ($this->activations as $activation) {
            $qsos = $activation->getQsos();
            $s2sQsos = array_filter($qsos, function (Qso $qso) {
                return !empty($qso->getS2sSummitCode());
            });

            // $activationData = $activation->jsonSerialize();


            // /** @var array{date: string, time: string, callsign: string, s2sSummitCode: string, mode: string, band: string, comments: string}[] */
            // $s2sQsos = array_filter($activationData['qsos'], function (array $qso) {
            //     return !empty($qso['s2sSummitCode']);
            // });
            foreach ($s2sQsos as $qso) {
                $chase = new Chase()
                    ->date($qso->getDate())
                    ->timeStr($qso->getTime())
                    ->otherCallsign($qso->getCallsign())
                    ->ownCallsign($activation->getOwnCallsign())
                    ->s2sSummitCode($qso->getS2sSummitCode())
                    ->summitCode($activation->getSummit())
                    ->mode($qso->getMode())
                    ->band($qso->getBand())
                    ->comments($qso->getComments());
                // ->date($qso['date'])
                // ->timeStr($qso['time'])
                // ->otherCallsign($qso['callsign'])
                // ->ownCallsign($activation['ownCallsign'])
                // ->s2sSummitCode($qso['s2sSummitCode'])
                // ->summitCode($activation['summit'])
                // ->mode($qso['mode'])
                // ->band($qso['band'])
                // ->comments($qso['comments']);
                array_push($s2s, $chase);
            }
        }
        return [
            'activations' => array_map(function (Activation $a) {
                return $a->jsonSerialize();
            }, $this->activations),
            's2s' => array_map(function(Chase $c) {
                return $c->jsonSerialize();
            }, $s2s),
            'chases' => array_map(function (Chase $c) {
                return $c->jsonSerialize();
            }, $this->chases),
        ];
    }
}
