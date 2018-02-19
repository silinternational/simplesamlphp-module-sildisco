<?php

$waitTime = 15;

$I = new AcceptanceTester($scenario);
$I->wantTo("Ensure I see the hub's metadata page.");

// default format (php)
$I->amOnUrl('http://hub4tests/module.php/sildisco/metadata.php');
$I->waitForText('$metadata[\'hub4tests\'] ', $waitTime);

// xml format
$I->amOnUrl('http://hub4tests/module.php/sildisco/metadata.php?format=xml');
$I->canSeeInSource('entityID="hub4tests"', $waitTime);

// php format
$I->amOnUrl('http://hub4tests/module.php/sildisco/metadata.php?format=php');
$I->waitForText('$metadata[\'hub4tests\'] ', $waitTime);

