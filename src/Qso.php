<?php

declare(strict_types=1);

namespace QueenAurelia\SOTA_Client;

use DateTimeImmutable;
use JsonSerializable;

/**
 * Encapsulates a QSO record
 */
final class Qso implements JsonSerializable
{
    private DateTimeImmutable $_date;
    private string $_time = "";
    private string $_callsign = "";
    private string $_s2sSummitCode = "";
    private string $_mode = "";
    private string $_band = "";
    private string $_comments = "";

    /**
     * @psalm-api
     * 
     * @param string $d Date the QSO occurred
     */
    public function date(string $d): self
    {
        $this->_date = new DateTimeImmutable($d);
        return $this;
    }
    
    /**
     * @psalm-api
     * 
     * Get the date of this QSO
     */
    // Mostly for internal use
    public function getDate(): DateTimeImmutable
    {
        return $this->_date;
    }

    /**
     * @psalm-api
     * 
     * @param string $t time the QSO occurred
     */
    public function time(string $t): self
    {
        $this->_time = $t;
        return $this;
    }

    /**
     * @psalm-api
     */
    public function getTime(): string
    {
        return $this->_time;
    }

    /**
     * @psalm-api
     * 
     * @param string $c Call sign of the station contacted
     */
    public function callsign(string $c): self
    {
        $this->_callsign = $c;
        return $this;
    }

    public function getCallsign(): string 
    {
        return $this->_callsign;
    }

    /**
     * @psalm-api
     * 
     * @param string $s Summit code (if applicable) of the station contacted
     */
    public function s2sSummitCode(string $s): self
    {
        $this->_s2sSummitCode = $s;
        return $this;
    }
    
    /**
     * @psalm-api
     * 
     * Get the summit code (if applicable) of the station contacted.
     */
    // Mostly for internal use
    public function getS2sSummitCode(): string
    {
        return $this->_s2sSummitCode;
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

    public function getMode(): string
    {
        return $this->_mode;
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
    
    public function getBand(): string {
        return $this->_band;
    }

    /**
     * @psalm-api
     * 
     * @param string $c Comments regarding the QSO
     */
    public function comments(string $c): self
    {
        $this->_comments = $c;
        return $this;
    }

    public function getComments(): string {
        return $this->_comments;
    }
    
    /**
     * Serialize this QSO record to JSON
     *
     * @return (string)[]
     *
     * @psalm-return array{date: string, time: string, callsign: string, s2sSummitCode: string, mode: string, band: string, comments: string}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return array(
            'date' => $this->_date->format("d/m/Y"),
            'time' => $this->_time,
            'callsign' => $this->_callsign,
            's2sSummitCode' => $this->_s2sSummitCode,
            'mode' => $this->_mode,
            'band' => $this->_band,
            'comments' => $this->_comments,
        );
    }
}
