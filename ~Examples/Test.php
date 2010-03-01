<?php

/* Ignore this file, okay? It's supposed to be used for the Framework demos.
And frameworks right now are nonsensical, but working. */

require 'System/Framework.php';

$_ROFL = true;

$sInput = '$this->Message("#ffs", "hai");'; 

$frame = new Framework('OUTRAGEbot');
$frame->Loop($_ROFL, $sInput);
