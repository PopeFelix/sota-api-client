<?php

declare(strict_types=1);

namespace QueenAurelia\SOTA_Client;

use DateTimeImmutable;
use JsonSerializable;
use QueenAurelia\SOTA_Client\Exception\InvalidArgumentException;
use Exception;

/**
 * @psalm-api
 * 
 * This encapsulates a SOTA Activation record.
 * 
 * // $qsos is an array of Qso instances
 * $a = new Activation()->date("2025-05-29")->summit("W3/PW-024")->ownCallsign("W0KEH")->qsos($qsos);
 * 
 */
final class Activation implements JsonSerializable
{
    private DateTimeImmutable $_date;
    private string $_summit;
    private string $_ownCallsign;
    /** @var QueenAurelia\SOTA_Client\Qso[] */
    private array $_qsos;

    /**
     * @psalm-api
     * 
     * Set the date of this activation
     * 
     * @param string $d the date of the activation
     */
    public function date(string $d): self {
        $this->_date = new DateTimeImmutable($d);
        return $this;
    }

    /**
     * @psalm-api
     * 
     * Set the summit code for this activation
     * 
     * @param string $s Summit code
     */
    public function summit(string $s): self {
        $this->_summit = $s;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * Set the activator's call sign
     * 
     * @param string $callsign The activator's call sign
     */
    public function ownCallsign(string $callsign): self {
        $this->_ownCallsign = $callsign;
        return $this;
    }

    /**
     * @psalm-api
     * 
     * Set the QSOs for this activation
     * 
     * @param array $qsos QSOs
     */
    public function qsos(array $qsos): self {
        if (!array_all($qsos, function (Qso $qso) {
            return $qso instanceof Qso;
        })) {
            throw new InvalidArgumentException("All items must be instances of Qso");
        }
        $this->_qsos = $qsos;
        return $this;
    }

    /** 
     * @psalm-api
     * 
     * Get the QSOs in this activation
     */
    public function getQsos(): array {
        return $this->_qsos;
    }
   
    public function getOwnCallsign(): string {
        return $this->_ownCallsign;
    }

    public function getSummit(): string {
        return $this->_summit;
    }
    
    /**
     * @psalm-api
     * 
     * Serialize this activation record to JSON
     *
     * @return (array|string)[]
     *
     * @psalm-return array{date: string, summit: string, ownCallsign: string, qsos: array}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return array(
            'date' => $this->_date->format("d/m/Y"),
            'summit' => $this->_summit,
            'ownCallsign' => $this->_ownCallsign,
            'qsos' => array_map(function(Qso $qso) {
                return $qso->jsonSerialize();
            }, $this->_qsos)
        );
    }
}
