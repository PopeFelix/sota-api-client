# SOTA API Client

A client for integrating with the [Summits On The Air](https://www.sota.org.uk/) (SOTA) database.

## Quickstart

```php
<?php

declare(strict_types=1);

use QueenAurelia\SOTA_Client\Client;
use QueenAurelia\SOTA_Client\Activation;
use QueenAurelia\SOTA_Client\Chase;
use QueenAurelia\SOTA_Client\Qso;

$client = new Client(
    [
        'client_id' => 'my-client-id', # You get this from the SOTA people
        'username' => 'w1aw', # Your SOTA database username, will probably be your callsign
        'password' => 'my-password', # Your SOTA database password
    ]
);

$qso = new Qso()
    ->callsign("K0GQ") # Call sign of the station contacted
    ->date("2025-06-18") # Date of the QSO
    ->time("23:23") # Time of the QSO
    ->mode("SSB") # Mode used to make the QSO
    ->band("14.310Mhz") # Band the QSO was made on
    ->s2sSummitCode("W0I/IA-001"); # Remote summit for S2S (summit-to-summit) QSOs

$activation = new Activation()
    ->date("2025-06-18") # Date of the activation
    ->ownCallsign("w1aw") # Your call sign
    ->summit("W0I/IA-002") # Summit you're activating
    ->qsos([$qso]);

$chase = new Chase() 
    ->date("2025-06-18") # Date of the chase
    ->timeStr("23:23") # Time of the chase
    ->ownCallsign('W1AW') # Your callsign
    ->otherCallsign('K0HAM') # Call sign of the station contacted
    ->summitCode("W0I/IA-003") # Location of activator or remote summit if S2S
    ->s2sSummitCode("W0I/IA-002") # Location of user/local summit for S2S
    ->band("14.310MHz") # Band the contact was made on
    ->mode("SSB") # Mode used to make the contact
    ->comments("59"); # comments regarding the contact

$client->addActivation($activation);
$client->addChase($chase);
$client->upload(); # Upload activation and chase to SOTA
```

## Objects

### Client

This is the class that actually uploads data to the SOTA database.

Methods:

#### constructor

Initializes the client. Parameters:

- *client_id* This is the client ID assigned to you by the SOTA database admins. 
- *username* Your SOTA database username. Generally your callsign.
- *password* Your SOTA database password.

#### addActivation()

Add an [Activation](#activation) to be uploaded. This may be called multiple times to add multiple activations.

#### addChase()

Add a [Chase](#chase) to be uploaded. This may be called multiple times to add multiple chases.

#### upload()

Upload the current set of records to the SOTA database. Note that S2S QSOs will automatically be extracted from the set of QSOs in the activation.

### Activation

This represents a SOTA activation. It includes one or more [QSOs](#qso).

```php
<?php

use QueenAurelia\SOTA_Client\Activation;
use QueenAurelia\SOTA_Client\Qso;

$activation = new Activation()
    ->date("2025-06-18") # Date of the activation
    ->ownCallsign("w1aw") # Your call sign
    ->summit("W0I/IA-002") # Summit you're activating
    ->qsos([new Qso()->
        ->callsign("K0GQ") # Call sign of the station contacted
        ->date("2025-06-18") # Date of the QSO
        ->time("23:23") # Time of the QSO
        ->mode("SSB") # Mode used to make the QSO
        ->band("14.310Mhz") # Band the QSO was made on
    ]);

```

### Chase

This represents a chase (i.e. a spot) of a SOTA activation

Usage:

```php
<?php

use QueenAurelia\SOTA_Client\Qso;

declare(strict_types=1);

$chase = new Chase() 
    ->date("2025-06-18") # Date of the chase
    ->timeStr("23:23") # Time of the chase
    ->ownCallsign('W1AW') # Your callsign
    ->otherCallsign('K0HAM') # Call sign of the station contacted
    ->summitCode("W0I/IA-003") # Location of activator or remote summit if S2S
    ->s2sSummitCode("W0I/IA-002") # Location of user/local summit for S2S
    ->band("14.310MHz") # Band the contact was made on
    ->mode("SSB") # Mode used to make the contact
    ->comments("59"); # comments regarding the contact

```

### Qso

This represents a QSO (i.e. amateur radio contact) made during a SOTA activation.

Usage:

```php
<?php

use QueenAurelia\SOTA_Client\Qso;

declare(strict_types=1);


$qso = new Qso()
    ->callsign("K0GQ") # Call sign of the station contacted
    ->date("2025-06-18") # Date of the QSO
    ->time("23:23") # Time of the QSO
    ->mode("SSB") # Mode used to make the QSO
    ->band("14.310Mhz") # Band the QSO was made on
    ->s2sSummitCode("W0I/IA-001") # Remote summit for S2S (summit-to-summit) QSOs
    ->comments("59"); # Comments regarding the QSO
```
