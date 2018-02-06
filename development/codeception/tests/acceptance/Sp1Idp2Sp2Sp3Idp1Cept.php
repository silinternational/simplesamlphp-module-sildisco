<?php 

$waitTime = 10;
$idp2Id =  '//*[@id="http://ssp-hub-idp2.local:8086"]';
$spHomePath = '/module.php/core/frontpage_welcome.php';

$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure I can login to Sp1 through Idp2, am already logged in for Sp2, and must login to Sp3 through Idp1.');

// Start at sp1
$I->amOnUrl('http://sp1' . $spHomePath);
$I->waitForText('About SimpleSAMLphp', $waitTime);

$I->click('Authentication');
$I->click('Test configured authentication sources');
$I->waitForText('Test authentication sources', $waitTime);

// Go to the hub
$I->click('hub4tests');
$I->waitForElement($idp2Id, $waitTime);

$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Use idp2 for Authentication
$I->click($idp2Id . "/parent::*");
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'b');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("http://ssp-hub-sp.local", $waitTime);


// Start at sp2. Go through hub to idp2
$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("http://ssp-hub-sp2.local", $waitTime);

// See that going to sp3 results bypassing the hub and going straight to idp1
$I->amOnUrl('http://sp3/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'a');
$I->click('//*[@id="submit"]/td[3]/button');


$I->waitForText("test_admin@idp1.org", $waitTime);

// Logout of both IDP's
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);


$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("http://ssp-hub-sp2.local", $waitTime);
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);
