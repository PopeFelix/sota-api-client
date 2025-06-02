<?php

declare(strict_types=1);

namespace QueenAurelia\SOTA_Client;

use DateTimeImmutable;
use JsonSerializable;

/**
 * Encapsulates a chase (i.e. a QSO with an SOTA activator)
 */
final class Chase implements JsonSerializable
{
    private DateTimeImmutable $_date;
    private string $_otherCallsign = "";
    private string $_ownCallsign = "";
    private string $_s2sSummitCode = "";
    private string $_summitCode = "";
    private string $_band = "";
    private string $_mode = "";
    private string $_comments = "";
    private string $_timeStr = "";

    /**
     * @psalm-api
     * 
     * @param string $d Date the chase QSO occurred
     */
    public function date(string|DateTimeImmutable $d): self
    {
        if ($d instanceof DateTimeImmutable) {
            $this->_date = $d;
        } else {
            $this->_date = new DateTimeImmutable($d);
        }
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $t time the chase QSO occurred
     */
    public function timeStr(string $t): self
    {
        $this->_timeStr = $t;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $c Chaser's call sign
     */
    public function ownCallsign(string $c): self
    {
        $this->_ownCallsign = $c;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $c Call sign of the station contacted
     */
    public function otherCallsign(string $c): self
    {
        $this->_otherCallsign = $c;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $s For S2S contacts, the remote summit code. For non-S2S contacts, the activator's summit code.
     */
    public function s2sSummitCode(string $s): self
    {
        $this->_s2sSummitCode = $s;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $s For S2S contacts, the local summit code (i.e. where the chaser is located). Unused otherwise.
     */
    public function summitCode(string $s): self
    {
        $this->_summitCode = $s;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $m Mode by which the contact was made
     */
    public function mode(string $m): self
    {
        $this->_mode = $m;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $b Band on which the contact was made
     */
    public function band(string $b): self
    {
        $this->_band = $b;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * @param string $c Comments regarding the chase QSO
     */
    public function comments(string $c): self
    {
        $this->_comments = $c;
        return $this;
    }

    /**
     * Serialize this QSO record to JSON
     *
     * @return (string)[]
     *
     * @psalm-return array{date: string, timeStr: string, otherCallsign: string, ownCallsign: string, s2sSummitCode: string, summitCode: string, mode: string, band: string, comments: string}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'date' => $this->_date->format("d/m/Y"),
            'timeStr' => $this->_timeStr,
            'otherCallsign' => $this->_otherCallsign,
            'ownCallsign' => $this->_ownCallsign,
            's2sSummitCode' => $this->_s2sSummitCode,
            'summitCode' => $this->_summitCode,
            'mode' => $this->_mode,
            'band' => $this->_band,
            'comments' => $this->_comments,
        ];
    }
}
